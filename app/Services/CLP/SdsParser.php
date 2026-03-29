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

        // Normalise line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $this->parseSection2($document->oil_id, $text);
        $this->parseSection3($document->oil_id, $text);

        $document->update(['parsed' => true]);

        return true;
    }

    // -------------------------------------------------------------------------
    // Extract Section 2 block only
    // -------------------------------------------------------------------------
    protected function extractSection2(string $text): string
    {
        // Match from "Section 2" up to "Section 3"
        // Nikura format uses "Section 2. Hazards identification"
        if (preg_match(
            '/Section\s+2[\.\s].*?(?=Section\s+3[\.\s])/si',
            $text,
            $match
        )) {
            return $match[0];
        }

        return '';
    }

    // -------------------------------------------------------------------------
    // Section 2 → oil_hazards
    // -------------------------------------------------------------------------
    protected function parseSection2(int $oilId, string $text): void
    {
        OilHazard::where('oil_id', $oilId)->delete();

        $section2 = $this->extractSection2($text);

        if (empty($section2)) {
            logger()->warning("SDS Parser: Could not isolate Section 2 for oil_id={$oilId}");
            return;
        }

        // --- Signal word ---
        // Nikura format: "Signal word:          Danger"
        $signalWord = null;
        if (preg_match('/Signal\s+word\s*:\s*(Danger|Warning)/i', $section2, $m)) {
            $signalWord = ucfirst(strtolower($m[1]));
        }

        // --- Hazard statements block ---
        // Nikura format lists them under "Hazard statements:" like:
        //   H304, May be fatal if swallowed and enters airways.
        //   H315, Causes skin irritation.
        // We extract only that block, stopping at the next labelled field.
        $hazardLines = $this->extractHazardStatementsBlock($section2);

        // Parse each line: "H317, May cause an allergic skin reaction."
        foreach ($hazardLines as $line) {
            if (!preg_match('/\b(H\d{3}(?:\+H\d{3})*)\b/', $line, $m)) {
                continue;
            }

            $code = $m[1];
            $meta = $this->hazardCodeMeta($code);

            // Use signal word from Section 2; fall back to meta default
            $resolvedSignal = $signalWord ?? ($meta['signal_word'] ?? null);

            OilHazard::create([
                'oil_id'       => $oilId,
                'hazard_class' => $meta['class'] ?? null,
                'category'     => $meta['category'] ?? null,
                'hazard_code'  => $code,
                'signal_word'  => $resolvedSignal,
                'pictogram'    => $meta['pictogram'] ?? null,
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Extract H-code lines from Section 2 label elements (2.2)
    //
    // Handles two Nikura SDS formats:
    //
    // FORMAT A (e.g. Fresh Linen) — codes listed AFTER "Hazard statements:" label:
    //   Signal word:         Danger
    //   Hazard statements:   H304, May be fatal...
    //                        H315, Causes skin irritation.
    //   Supplemental...
    //
    // FORMAT B (e.g. Lemon v2) — codes listed BEFORE "Hazard statements:" label,
    // immediately after the Signal word line:
    //   Signal word:         Danger
    //   H226, Flammable liquid and vapour.
    //   H304, May be fatal...
    //   Hazard statements:   (label appears again, codes repeat)
    //   M factor:            None
    //   Supplemental...
    //
    // Strategy: anchor on "Signal word:" and collect all H-code lines between
    // there and the first non-hazard field (M factor / Supplemental / Precautionary).
    // This captures both formats since the label element H-codes always follow
    // the signal word in both cases, and we deduplicate before returning.
    // -------------------------------------------------------------------------
    protected function extractHazardStatementsBlock(string $section2): array
    {
        // Find the section 2.2 label elements block — anchor on Signal word
        // Capture everything from Signal word up to Precautionary statements / Pictograms
        if (!preg_match(
            '/Signal\s+word\s*:.*?(?=(?:Precautionary\s+statements?|Pictograms?|2\.3|Other\s+hazards?|\Z))/si',
            $section2,
            $labelBlock
        )) {
            // No Signal word found — fall back to any H-code line in section 2
            preg_match_all('/^\s*(H\d{3}[^\n]*)/m', $section2, $m);
            return array_values(array_unique($m[1] ?? []));
        }

        $block = $labelBlock[0];

        // Collect every line containing an H-code
        $lines = array_filter(
            explode("\n", $block),
            fn ($line) => preg_match('/\bH\d{3}\b/', $line)
        );

        // Deduplicate by H-code — keep first occurrence of each code
        $seen   = [];
        $unique = [];
        foreach ($lines as $line) {
            if (preg_match('/\b(H\d{3})\b/', $line, $m)) {
                if (!in_array($m[1], $seen)) {
                    $seen[]   = $m[1];
                    $unique[] = trim($line);
                }
            }
        }

        return $unique;
    }

    // -------------------------------------------------------------------------
    // Extract Section 3 block only
    // -------------------------------------------------------------------------
    protected function extractSection3(string $text): string
    {
        // Match from "Section 3" up to "Section 4"
        if (preg_match(
            '/Section\s+3[\.\s].*?(?=Section\s+4[\.\s])/si',
            $text,
            $match
        )) {
            return $match[0];
        }

        return '';
    }

    // -------------------------------------------------------------------------
    // Section 3 → oil_components
    //
    // Nikura table columns (from the actual PDF):
    //   Name | CAS | EC | REACH Registration No. | % | Classification (CLP) | Specific Conc. Limits...
    //
    // Concentration format: "20-<50%" or "10-<20%" or "0.1-<1%" or "1-<5%"
    // -------------------------------------------------------------------------
    protected function parseSection3(int $oilId, string $text): void
    {
        OilComponent::where('oil_id', $oilId)->delete();

        $section3 = $this->extractSection3($text);

        if (empty($section3)) {
            logger()->warning("SDS Parser: Could not isolate Section 3 for oil_id={$oilId}");
            return;
        }

        // Match ingredient rows. Nikura format has CAS numbers like:
        //   1222-05-5, 5989-27-5, 475-20-7 (sometimes two CAS on one row)
        // Concentration like: 20-<50%  10-<20%  5-<10%  1-<5%  0.1-<1%
        //
        // Pattern: capture NAME then CAS then optional EC then % range then H-codes
        $pattern = '/
            ([A-Za-z][A-Za-z0-9\s\-\(\),\.\/\']+?)   # ingredient name
            \s+
            (\d{2,7}-\d{2}-\d                          # primary CAS
            (?:,\s*\d{2,7}-\d{2}-\d)?)                # optional second CAS
            \s+
            (?:\d{3}-\d{3}-\d\s+)?                    # optional EC number
            (?:[0-9A-Z\/\-]+\s+)?                     # optional REACH reg no
            (\d+\.?\d*)\s*-\s*[<>]?\s*(\d+\.?\d*)\s*% # concentration range
            \s+
            ((?:[A-Z][a-z]+[\.\s]+)*                  # CLP text (class names)
            (?:H\d{3}[\-,\s]*)+)                      # H-codes in classification
        /x';

        preg_match_all($pattern, $section3, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name    = trim($match[1]);
            $cas     = trim(explode(',', $match[2])[0]); // use first CAS if two listed
            $min     = (float) $match[3];
            $max     = (float) $match[4];
            $clpRaw  = $match[5];

            // Extract just the H-codes from the classification column
            preg_match_all('/H\d{3}/', $clpRaw, $hCodes);
            $clp = !empty($hCodes[0]) ? implode(', ', array_unique($hCodes[0])) : null;

            OilComponent::create([
                'oil_id'             => $oilId,
                'name'               => $name,
                'cas'                => $cas,
                'concentration_min'  => $min,
                'concentration_max'  => $max,
                'clp_classification' => $clp,
            ]);
        }

        // If the regex approach got nothing (PDF text extraction can be messy),
        // fall back to a simpler CAS + percentage scan within Section 3 only
        if (OilComponent::where('oil_id', $oilId)->count() === 0) {
            $this->parseSection3Fallback($oilId, $section3);
        }
    }

    // -------------------------------------------------------------------------
    // Fallback Section 3 parser — less structured but more tolerant
    // -------------------------------------------------------------------------
    protected function parseSection3Fallback(int $oilId, string $section3): void
    {
        // Find all CAS numbers in the section
        preg_match_all('/(\d{2,7}-\d{2}-\d)/', $section3, $casMatches, PREG_OFFSET_CAPTURE);

        foreach ($casMatches[1] as $casMatch) {
            $cas    = $casMatch[0];
            $offset = $casMatch[1];

            // Grab the surrounding 300 chars to find % and H-codes
            $context = substr($section3, max(0, $offset - 100), 400);

            // Find concentration range near this CAS
            if (!preg_match('/(\d+\.?\d*)\s*-\s*[<>]?\s*(\d+\.?\d*)\s*%/', $context, $concMatch)) {
                continue;
            }

            // Try to find the ingredient name (text before the CAS number)
            $before = substr($section3, max(0, $offset - 80), 80);
            preg_match('/([A-Za-z][A-Za-z0-9\s\-\(\)]+?)\s*$/', trim($before), $nameMatch);
            $name = isset($nameMatch[1]) ? trim($nameMatch[1]) : 'Unknown';

            // H-codes in the context
            preg_match_all('/H\d{3}/', $context, $hCodes);
            $clp = !empty($hCodes[0]) ? implode(', ', array_unique($hCodes[0])) : null;

            OilComponent::create([
                'oil_id'             => $oilId,
                'name'               => $name,
                'cas'                => $cas,
                'concentration_min'  => (float) $concMatch[1],
                'concentration_max'  => (float) $concMatch[2],
                'clp_classification' => $clp,
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Hazard code metadata
    // Maps H-code → class, category, default signal word, pictogram
    // Extend this as you encounter new codes in your SDS sheets
    // -------------------------------------------------------------------------
    protected function hazardCodeMeta(string $code): array
    {
        $map = [
            // Skin/Eye
            'H315' => ['class' => 'Skin Irritation',           'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H317' => ['class' => 'Skin Sensitisation',        'category' => '1B', 'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H319' => ['class' => 'Eye Irritation',            'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H318' => ['class' => 'Eye Damage',                'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'corrosion'],
            'H314' => ['class' => 'Skin Corrosion',            'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'corrosion'],
            // Aspiration / Acute Tox
            'H304' => ['class' => 'Aspiration Hazard',         'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'health-hazard'],
            'H302' => ['class' => 'Acute Toxicity Oral',       'category' => '4',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H312' => ['class' => 'Acute Toxicity Dermal',     'category' => '4',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H332' => ['class' => 'Acute Toxicity Inhalation', 'category' => '4',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            // Flammable
            'H226' => ['class' => 'Flammable Liquid',          'category' => '3',  'signal_word' => 'Warning', 'pictogram' => 'flame'],
            'H225' => ['class' => 'Flammable Liquid',          'category' => '2',  'signal_word' => 'Danger',  'pictogram' => 'flame'],
            'H224' => ['class' => 'Flammable Liquid',          'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'flame'],
            'H227' => ['class' => 'Combustible Liquid',        'category' => '4',  'signal_word' => 'Warning', 'pictogram' => 'flame'],
            // Aquatic
            'H400' => ['class' => 'Aquatic Acute',             'category' => '1',  'signal_word' => 'Warning', 'pictogram' => 'environment'],
            'H410' => ['class' => 'Aquatic Chronic',           'category' => '1',  'signal_word' => 'Warning', 'pictogram' => 'environment'],
            'H411' => ['class' => 'Aquatic Chronic',           'category' => '2',  'signal_word' => null,      'pictogram' => 'environment'],
            'H412' => ['class' => 'Aquatic Chronic',           'category' => '3',  'signal_word' => null,      'pictogram' => null],
            // Reproductive / STOT
            'H361' => ['class' => 'Reproductive Toxicity',     'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'health-hazard'],
            'H360' => ['class' => 'Reproductive Toxicity',     'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'health-hazard'],
            'H336' => ['class' => 'STOT Single Exposure',      'category' => '3',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H335' => ['class' => 'STOT Single Exposure',      'category' => '3',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H373' => ['class' => 'STOT Repeated Exposure',    'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'health-hazard'],
            'H372' => ['class' => 'STOT Repeated Exposure',    'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'health-hazard'],
        ];

        return $map[$code] ?? [];
    }
}
