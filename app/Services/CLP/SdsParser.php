<?php

namespace App\Services\CLP;

use App\Models\Product;
use App\Models\SDSDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Oil;
use App\Models\OilHazard;
use App\Models\OilComponent;
use Smalot\PdfParser\Parser;

class SdsParser
{
    public function parse(SDSDocument $document): bool
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile(Storage::disk('local')->path($document->file_path));
        $text   = $pdf->getText();

        $this->parseSection2($document->oil_id, $text);
        $this->parseSection3($document->oil_id, $text);

        $document->update(['parsed' => true]);

        return true;
    }

    // -------------------------------------------------------
    // Hazard Identification
    // -------------------------------------------------------
    protected function parseSection2(int $oilId, string $text): void
    {
        // Delete existing so re-parsing is idempotent
        OilHazard::where('oil_id', $oilId)->delete();

        // Hazard statements — match patterns like H317, H315, etc.
        preg_match_all('/\b(H\d{3}\+?(?:i)?)\b/', $text, $hMatches);
        $hCodes = array_unique($hMatches[1]);

        // Signal word detection
        $signalWord = null;
        if (stripos($text, 'Danger') !== false) {
            $signalWord = 'Danger';
        } elseif (stripos($text, 'Warning') !== false) {
            $signalWord = 'Warning';
        }

        // Build a simple hazard code → class/pictogram map
        $hazardMeta = $this->hazardCodeMeta();

        foreach ($hCodes as $code) {
            $meta = $hazardMeta[$code] ?? [];

            OilHazard::create([
                'oil_id'       => $oilId,
                'hazard_class' => $meta['class'] ?? null,
                'category'     => $meta['category'] ?? null,
                'hazard_code'  => $code,
                'signal_word'  => $meta['signal_word'] ?? $signalWord,
                'pictogram'    => $meta['pictogram'] ?? null,
            ]);
        }
    }

    // -------------------------------------------------------
    // Composition / Ingredients
    // -------------------------------------------------------
    protected function parseSection3(int $oilId, string $text): void
    {
        OilComponent::where('oil_id', $oilId)->delete();

        // Extract section 3 block
        preg_match('/SECTION\s+3.*?(?=SECTION\s+4)/si', $text, $section);
        $block = $section[0] ?? $text;

        // Match lines like: Linalool | 78-70-6 | 20–<50% | Flam. Liq. 3, H226
        // Nikura format often has CAS numbers and % ranges
        $pattern = '/([A-Za-z][A-Za-z\s\-,()]+?)\s+(\d{2,7}-\d{2}-\d)\s+[\s|]*(\d+\.?\d*)\s*[–\-]\s*[<>]?\s*(\d+\.?\d*)\s*%/';

        preg_match_all($pattern, $block, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            // Extract CLP classification from same line if present
            $linePattern = '/' . preg_quote($match[2], '/') . '.{0,150}/s';
            preg_match($linePattern, $block, $lineMatch);
            $clp = $this->extractClpFromLine($lineMatch[0] ?? '');

            OilComponent::create([
                'oil_id'            => $oilId,
                'name'              => trim($match[1]),
                'cas'               => $match[2],
                'concentration_min' => (float) $match[3],
                'concentration_max' => (float) $match[4],
                'clp_classification' => $clp,
            ]);
        }
    }

    protected function extractClpFromLine(string $line): ?string
    {
        // Pull H-codes from the component line
        preg_match_all('/\bH\d{3}\b/', $line, $m);
        return $m[0] ? implode(', ', $m[0]) : null;
    }

    // -------------------------------------------------------
    // Static metadata for known H-codes
    // -------------------------------------------------------
    protected function hazardCodeMeta(): array
    {
        return [
            'H315' => ['class' => 'Skin Irritation',    'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H317' => ['class' => 'Skin Sensitisation', 'category' => '1B', 'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H319' => ['class' => 'Eye Irritation',     'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H361' => ['class' => 'Reproductive Tox',   'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'health-hazard'],
            'H400' => ['class' => 'Aquatic Acute',      'category' => '1',  'signal_word' => 'Warning', 'pictogram' => 'environment'],
            'H410' => ['class' => 'Aquatic Chronic',    'category' => '1',  'signal_word' => 'Warning', 'pictogram' => 'environment'],
            'H226' => ['class' => 'Flammable Liquid',   'category' => '3',  'signal_word' => 'Warning', 'pictogram' => 'flame'],
            'H302' => ['class' => 'Acute Toxicity Oral','category' => '4',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
        ];
    }
}
