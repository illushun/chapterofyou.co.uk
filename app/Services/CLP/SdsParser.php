<?php

namespace App\Services\CLP;

use App\Models\Oil;
use App\Models\OilHazard;
use App\Models\OilComponent;
use App\Models\SDSDocument;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class SdsParser
{
    public function parse(SDSDocument $document): bool
    {
        $filePath = Storage::disk('local')->path($document->file_path);

        // Section 2 — use smalot/pdfparser (text extraction, regex-based)
        $parser = new Parser();
        $pdf    = $parser->parseFile($filePath);
        $text   = str_replace(["\r\n", "\r"], "\n", $pdf->getText());

        $this->parseSection2($document->oil_id, $text);

        // Section 3 — use pdfplumber via Python subprocess for reliable table extraction
        $this->parseSection3WithPdfplumber($document->oil_id, $filePath);

        $document->update(['parsed' => true]);

        return true;
    }

    // -------------------------------------------------------------------------
    // Section 2 → oil_hazards  (unchanged — smalot works fine for this)
    // -------------------------------------------------------------------------
    protected function extractSection2(string $text): string
    {
        if (preg_match('/Section\s+2[\.\s].*?(?=Section\s+3[\.\s])/si', $text, $match)) {
            return $match[0];
        }
        return '';
    }

    protected function parseSection2(int $oilId, string $text): void
    {
        OilHazard::where('oil_id', $oilId)->delete();

        $section2 = $this->extractSection2($text);
        if (empty($section2)) {
            logger()->warning("SDS Parser: Could not isolate Section 2 for oil_id={$oilId}");
            return;
        }

        $signalWord = null;
        if (preg_match('/Signal\s+word\s*:\s*(Danger|Warning)/i', $section2, $m)) {
            $signalWord = ucfirst(strtolower($m[1]));
        }

        $hazardLines = $this->extractHazardStatementsBlock($section2);

        foreach ($hazardLines as $line) {
            if (!preg_match('/\b(H\d{3}(?:\+H\d{3})*)\b/', $line, $m)) {
                continue;
            }

            $code = $m[1];
            $meta = $this->hazardCodeMeta($code);

            OilHazard::create([
                'oil_id'       => $oilId,
                'hazard_class' => $meta['class']        ?? null,
                'category'     => $meta['category']     ?? null,
                'hazard_code'  => $code,
                'signal_word'  => $signalWord ?? ($meta['signal_word'] ?? null),
                'pictogram'    => $meta['pictogram']    ?? null,
            ]);
        }
    }

    protected function extractHazardStatementsBlock(string $section2): array
    {
        if (!preg_match(
            '/Signal\s+word\s*:.*?(?=(?:Precautionary\s+statements?|Pictograms?|2\.3|Other\s+hazards?|\Z))/si',
            $section2,
            $labelBlock
        )) {
            preg_match_all('/^\s*(H\d{3}[^\n]*)/m', $section2, $m);
            return array_values(array_unique($m[1] ?? []));
        }

        $block = $labelBlock[0];
        $lines = array_filter(
            explode("\n", $block),
            fn ($line) => preg_match('/\bH\d{3}\b/', $line)
        );

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
    // Section 3 → oil_components
    //
    // Uses pdfplumber (Python) via a subprocess for reliable table extraction.
    // pdfplumber correctly separates the "Name" column from the
    // "Specific Conc. Limits, M-factors and ATEs" column, preventing
    // ATE values ("mg/kg bw", "M = 1") from bleeding into ingredient names.
    //
    // Requires: pip install pdfplumber
    // -------------------------------------------------------------------------
    protected function parseSection3WithPdfplumber(int $oilId, string $pdfPath): void
    {
        OilComponent::where('oil_id', $oilId)->delete();

        // Inline Python script — extracts all tables, finds the Section 3
        // ingredient table by looking for a "Name" column header, and
        // outputs clean JSON rows.
        $pythonScript = <<<'PYTHON'
import sys, json, pdfplumber, re

pdf_path = sys.argv[1]
rows = []

with pdfplumber.open(pdf_path) as pdf:
    for page in pdf.pages:
        for table in page.extract_tables():
            if not table or not table[0]:
                continue
            headers = [str(h or '').lower().strip() for h in table[0]]
            # Find the Section 3 ingredient table — must have Name and CAS columns
            if 'name' not in headers or 'cas' not in headers:
                continue
            name_idx = headers.index('name')
            cas_idx  = headers.index('cas')
            # Find % column (header contains %)
            pct_idx = next((i for i, h in enumerate(headers) if '%' in h), None)
            # Find CLP classification column
            clp_idx = next((i for i, h in enumerate(headers) if 'classif' in h or 'clp' in h or '1272' in h), None)

            for row in table[1:]:
                if not row or not row[name_idx]:
                    continue
                name = str(row[name_idx] or '').replace('\n', ' ').strip()
                cas  = str(row[cas_idx]  or '').replace('\n', ' ').strip()
                pct  = str(row[pct_idx]  or '').replace('\n', ' ').strip() if pct_idx is not None else ''
                clp  = str(row[clp_idx]  or '').replace('\n', ' ').strip() if clp_idx is not None else ''

                # Skip header-like rows or empty names
                if not name or name.lower() == 'name':
                    continue
                # Skip rows that look like they are ATE/transport table rows
                if not cas or not re.search(r'\d{2,7}-\d{2}-\d', cas):
                    continue

                # Parse concentration range — e.g. "50-100%", "10-<20%", "5-<10%", "1-<5%"
                conc_min, conc_max = None, None
                pct_clean = pct.replace(' ', '').replace('%','')
                m = re.match(r'(\d+\.?\d*)\s*-\s*[<>]?\s*(\d+\.?\d*)', pct_clean)
                if m:
                    conc_min = float(m.group(1))
                    conc_max = float(m.group(2))

                # Extract H-codes from CLP classification column
                h_codes = re.findall(r'H\d{3}', clp)
                clp_clean = ', '.join(dict.fromkeys(h_codes)) if h_codes else None

                rows.append({
                    'name': name,
                    'cas':  cas.split(',')[0].strip(),  # use first CAS if multiple
                    'concentration_min': conc_min,
                    'concentration_max': conc_max,
                    'clp_classification': clp_clean,
                })

print(json.dumps(rows))
PYTHON;

        // Write the script to a temp file
        $scriptPath = sys_get_temp_dir() . '/sds_section3_parser.py';
        file_put_contents($scriptPath, $pythonScript);

        // Run it
        $escapedPdf    = escapeshellarg($pdfPath);
        $escapedScript = escapeshellarg($scriptPath);
        $output        = shell_exec("python3 {$escapedScript} {$escapedPdf} 2>/dev/null");

        if (empty($output)) {
            logger()->warning("SDS Parser (pdfplumber): No output for oil_id={$oilId}. Falling back to regex.");
            $this->parseSection3Fallback($oilId, $pdfPath);
            return;
        }

        $rows = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($rows)) {
            logger()->warning("SDS Parser (pdfplumber): Invalid JSON for oil_id={$oilId}. Falling back.");
            $this->parseSection3Fallback($oilId, $pdfPath);
            return;
        }

        foreach ($rows as $row) {
            OilComponent::create([
                'oil_id'             => $oilId,
                'name'               => $row['name'],
                'cas'                => $row['cas'],
                'concentration_min'  => $row['concentration_min'],
                'concentration_max'  => $row['concentration_max'],
                'clp_classification' => $row['clp_classification'],
            ]);
        }

        logger()->info("SDS Parser: Section 3 parsed " . count($rows) . " components for oil_id={$oilId}");
    }

    // -------------------------------------------------------------------------
    // Fallback Section 3 parser — used if pdfplumber/Python is unavailable
    // Uses regex on raw PDF text — less accurate but tolerant
    // -------------------------------------------------------------------------
    protected function parseSection3Fallback(int $oilId, string $pdfPath): void
    {
        $parser   = new Parser();
        $pdf      = $parser->parseFile($pdfPath);
        $text     = str_replace(["\r\n", "\r"], "\n", $pdf->getText());

        // Extract section 3 block
        if (!preg_match('/Section\s+3[\.\s].*?(?=Section\s+4[\.\s])/si', $text, $match)) {
            return;
        }
        $section3 = $match[0];

        preg_match_all('/(\d{2,7}-\d{2}-\d)/', $section3, $casMatches, PREG_OFFSET_CAPTURE);

        foreach ($casMatches[1] as $casMatch) {
            $cas    = $casMatch[0];
            $offset = $casMatch[1];
            $context = substr($section3, max(0, $offset - 100), 400);

            if (!preg_match('/(\d+\.?\d*)\s*-\s*[<>]?\s*(\d+\.?\d*)\s*%/', $context, $concMatch)) {
                continue;
            }

            $before = substr($section3, max(0, $offset - 80), 80);
            preg_match('/([A-Za-z][A-Za-z0-9\s\-\(\)]+?)\s*$/', trim($before), $nameMatch);
            $name = isset($nameMatch[1]) ? trim($nameMatch[1]) : 'Unknown';

            // Clean up name — strip ATE column bleed
            $name = $this->cleanIngredientName($name);

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
    // Clean ingredient name — strip ATE column bleed artefacts
    //
    // When the regex fallback is used, the "Specific Conc. Limits, M-factors
    // and ATEs" column from the previous row can bleed into the ingredient name.
    // This strips known patterns that should never appear in a chemical name.
    // -------------------------------------------------------------------------
    protected function cleanIngredientName(string $name): string
    {
        // Remove ATE suffixes/prefixes that bleed from the last column
        $stripPatterns = [
            '/\b(mg\/kg\s*bw|kg\s*bw|bw)\b/i',                    // "mg/kg bw", "kg bw", "bw"
            '/\bM\s*=\s*\d+\b/i',                                   // "M = 1"
            '/\b(dermal|oral|inhalation)\s*:\s*ATE\s*=.*$/i',      // "dermal: ATE = ..."
            '/\bATE\s*=\s*[\d\.]+/i',                               // "ATE = 1610"
            '/\bSpecific\s+Conc\..*$/i',                             // column header bleed
            '/\bM-factors\s+and\s+ATEs\b/i',                        // column header bleed
            '/\bland\s+ATEs\b/i',                                    // partial header bleed
        ];

        foreach ($stripPatterns as $pattern) {
            $name = preg_replace($pattern, '', $name);
        }

        return trim($name);
    }

    // -------------------------------------------------------------------------
    // Hazard code metadata (unchanged)
    // -------------------------------------------------------------------------
    protected function hazardCodeMeta(string $code): array
    {
        $map = [
            'H315' => ['class' => 'Skin Irritation',           'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H317' => ['class' => 'Skin Sensitisation',        'category' => '1B', 'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H319' => ['class' => 'Eye Irritation',            'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H318' => ['class' => 'Eye Damage',                'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'corrosion'],
            'H314' => ['class' => 'Skin Corrosion',            'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'corrosion'],
            'H304' => ['class' => 'Aspiration Hazard',         'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'health-hazard'],
            'H302' => ['class' => 'Acute Toxicity Oral',       'category' => '4',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H312' => ['class' => 'Acute Toxicity Dermal',     'category' => '4',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H332' => ['class' => 'Acute Toxicity Inhalation', 'category' => '4',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H226' => ['class' => 'Flammable Liquid',          'category' => '3',  'signal_word' => 'Warning', 'pictogram' => 'flame'],
            'H225' => ['class' => 'Flammable Liquid',          'category' => '2',  'signal_word' => 'Danger',  'pictogram' => 'flame'],
            'H224' => ['class' => 'Flammable Liquid',          'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'flame'],
            'H227' => ['class' => 'Combustible Liquid',        'category' => '4',  'signal_word' => 'Warning', 'pictogram' => 'flame'],
            'H316' => ['class' => 'Skin Irritation',           'category' => '3',  'signal_word' => null,      'pictogram' => null],
            'H400' => ['class' => 'Aquatic Acute',             'category' => '1',  'signal_word' => 'Warning', 'pictogram' => 'environment'],
            'H401' => ['class' => 'Aquatic Acute',             'category' => '2',  'signal_word' => null,      'pictogram' => null],
            'H402' => ['class' => 'Aquatic Acute',             'category' => '3',  'signal_word' => null,      'pictogram' => null],
            'H410' => ['class' => 'Aquatic Chronic',           'category' => '1',  'signal_word' => 'Warning', 'pictogram' => 'environment'],
            'H411' => ['class' => 'Aquatic Chronic',           'category' => '2',  'signal_word' => null,      'pictogram' => 'environment'],
            'H412' => ['class' => 'Aquatic Chronic',           'category' => '3',  'signal_word' => null,      'pictogram' => null],
            'H413' => ['class' => 'Aquatic Chronic',           'category' => '4',  'signal_word' => null,      'pictogram' => null],
            'H300' => ['class' => 'Acute Toxicity Oral',       'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'skull'],
            'H301' => ['class' => 'Acute Toxicity Oral',       'category' => '3',  'signal_word' => 'Danger',  'pictogram' => 'skull'],
            'H310' => ['class' => 'Acute Toxicity Dermal',     'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'skull'],
            'H311' => ['class' => 'Acute Toxicity Dermal',     'category' => '3',  'signal_word' => 'Danger',  'pictogram' => 'skull'],
            'H330' => ['class' => 'Acute Toxicity Inhalation', 'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'skull'],
            'H331' => ['class' => 'Acute Toxicity Inhalation', 'category' => '3',  'signal_word' => 'Danger',  'pictogram' => 'skull'],
            'H361' => ['class' => 'Reproductive Toxicity',     'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'health-hazard'],
            'H360' => ['class' => 'Reproductive Toxicity',     'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'health-hazard'],
            'H362' => ['class' => 'Reproductive Toxicity',     'category' => null, 'signal_word' => null,      'pictogram' => null],
            'H336' => ['class' => 'STOT Single Exposure',      'category' => '3',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H335' => ['class' => 'STOT Single Exposure',      'category' => '3',  'signal_word' => 'Warning', 'pictogram' => 'exclamation'],
            'H371' => ['class' => 'STOT Single Exposure',      'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'health-hazard'],
            'H370' => ['class' => 'STOT Single Exposure',      'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'health-hazard'],
            'H373' => ['class' => 'STOT Repeated Exposure',    'category' => '2',  'signal_word' => 'Warning', 'pictogram' => 'health-hazard'],
            'H372' => ['class' => 'STOT Repeated Exposure',    'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'health-hazard'],
            'H280' => ['class' => 'Gases Under Pressure',      'category' => null, 'signal_word' => 'Warning', 'pictogram' => 'gas-cylinder'],
            'H281' => ['class' => 'Gases Under Pressure',      'category' => null, 'signal_word' => 'Warning', 'pictogram' => 'gas-cylinder'],
            'H200' => ['class' => 'Unstable Explosive',        'category' => null, 'signal_word' => 'Danger',  'pictogram' => 'explosion'],
            'H201' => ['class' => 'Explosive',                 'category' => '1.1','signal_word' => 'Danger',  'pictogram' => 'explosion'],
            'H202' => ['class' => 'Explosive',                 'category' => '1.2','signal_word' => 'Danger',  'pictogram' => 'explosion'],
            'H203' => ['class' => 'Explosive',                 'category' => '1.3','signal_word' => 'Danger',  'pictogram' => 'explosion'],
            'H204' => ['class' => 'Explosive',                 'category' => '1.4','signal_word' => 'Warning', 'pictogram' => 'explosion'],
            'H270' => ['class' => 'Oxidising Gas',             'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'oxidizer'],
            'H271' => ['class' => 'Oxidising Liquid',          'category' => '1',  'signal_word' => 'Danger',  'pictogram' => 'oxidizer'],
            'H272' => ['class' => 'Oxidising Liquid',          'category' => '3',  'signal_word' => 'Warning', 'pictogram' => 'oxidizer'],
        ];

        return $map[$code] ?? [];
    }
}
