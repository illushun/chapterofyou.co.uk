<?php

namespace App\Services\CLP;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CLPCalculator
{
    // -------------------------------------------------------------------------
    // Main entry point
    // Input:  Product model (with oils.hazards + materials loaded)
    // Output: Full CLP classification result for the FINAL mixture
    // -------------------------------------------------------------------------
    public function calculate(Product $product): array
    {
        $product->loadMissing(['oils.hazards', 'materials']);

        // Build a flat list of [ 'hazard_code' => code, 'percentage' => float ]
        // for every hazard of every oil in this product
        $ingredients = $this->buildIngredientHazardList($product);

        // Run each hazard class rule against the ingredient list
        $triggeredClasses = $this->classifyMixture($ingredients);

        // Derive H statements from triggered classes
        $hStatements = $this->deriveHStatements($triggeredClasses);

        // Derive P statements from H statements
        $pStatements = $this->derivePStatements(array_keys($hStatements));

        // Resolve signal word and pictograms from triggered classes
        $signalWord  = $this->resolveSignalWord($triggeredClasses);
        $pictograms  = $this->resolvePictograms($triggeredClasses);

        return [
            'signal_word'              => $signalWord,
            'required_pictograms'      => $pictograms,
            'hazard_statements'        => $hStatements,
            'precautionary_statements' => $pStatements,
            'triggered_classes'        => $triggeredClasses,
        ];
    }

    // -------------------------------------------------------------------------
    // Build flat ingredient hazard list from product relationships
    // Returns: array of [ 'code' => 'H317', 'percentage' => 6.0, 'signal' => 'Warning' ]
    // -------------------------------------------------------------------------
    protected function buildIngredientHazardList(Product $product): array
    {
        $list = [];

        foreach ($product->oils as $oil) {
            $percentage = (float) $oil->pivot->percentage;

            foreach ($oil->hazards as $hazard) {
                $list[] = [
                    'code'       => $hazard->hazard_code,
                    'percentage' => $percentage,
                    'signal'     => $hazard->signal_word,
                    'pictogram'  => $hazard->pictogram,
                ];
            }
        }

        return $list;
    }

    // -------------------------------------------------------------------------
    // Classify the mixture
    // Returns array of triggered hazard classes with their resolved category
    // e.g. [ 'skin_sensitisation' => ['category' => '1', 'signal' => 'Warning', ...] ]
    // -------------------------------------------------------------------------
    protected function classifyMixture(array $ingredients): array
    {
        $triggered = [];

        foreach ($this->hazardRules() as $classKey => $rule) {
            $result = $rule['evaluate']($ingredients);
            if ($result !== null) {
                $triggered[$classKey] = $result;
            }
        }

        return $triggered;
    }

    // -------------------------------------------------------------------------
    // Hazard classification rules
    // Each rule receives the full ingredient list and returns either:
    //   - null (not triggered)
    //   - array with category, signal_word, pictogram, h_code
    //
    // Thresholds based on CLP Regulation (EC) No 1272/2008 Annex I
    // -------------------------------------------------------------------------
    protected function hazardRules(): array
    {
        return [

            // -----------------------------------------------------------------
            // Skin Sensitisation — H317
            // Threshold: Cat 1 / 1A = 0.1%, Cat 1B = 0.1%
            // -----------------------------------------------------------------
            'skin_sensitisation' => [
                'evaluate' => function (array $ingredients): ?array {
                    $codes = ['H317'];
                    foreach ($ingredients as $i) {
                        if (in_array($i['code'], $codes) && $i['percentage'] >= 0.1) {
                            return [
                                'h_code'     => 'H317',
                                'category'   => '1',
                                'signal'     => 'Warning',
                                'pictogram'  => 'exclamation',
                            ];
                        }
                    }
                    return null;
                },
            ],

            // -----------------------------------------------------------------
            // Skin Irritation — H315
            // Threshold: 10% (generic cut-off for Cat 2)
            // -----------------------------------------------------------------
            'skin_irritation' => [
                'evaluate' => function (array $ingredients): ?array {
                    $codes = ['H315'];
                    foreach ($ingredients as $i) {
                        if (in_array($i['code'], $codes) && $i['percentage'] >= 10.0) {
                            return [
                                'h_code'    => 'H315',
                                'category'  => '2',
                                'signal'    => 'Warning',
                                'pictogram' => 'exclamation',
                            ];
                        }
                    }
                    return null;
                },
            ],

            // -----------------------------------------------------------------
            // Eye Irritation — H319
            // Threshold: 10%
            // -----------------------------------------------------------------
            'eye_irritation' => [
                'evaluate' => function (array $ingredients): ?array {
                    $codes = ['H319'];
                    foreach ($ingredients as $i) {
                        if (in_array($i['code'], $codes) && $i['percentage'] >= 10.0) {
                            return [
                                'h_code'    => 'H319',
                                'category'  => '2',
                                'signal'    => 'Warning',
                                'pictogram' => 'exclamation',
                            ];
                        }
                    }
                    return null;
                },
            ],

            // -----------------------------------------------------------------
            // Aspiration Hazard — H304
            // Threshold: 10% if ingredient is H304
            // Note: only applies if mixture is not water-based / emulsion
            // -----------------------------------------------------------------
            'aspiration_hazard' => [
                'evaluate' => function (array $ingredients): ?array {
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H304' && $i['percentage'] >= 10.0) {
                            return [
                                'h_code'    => 'H304',
                                'category'  => '1',
                                'signal'    => 'Danger',
                                'pictogram' => 'health-hazard',
                            ];
                        }
                    }
                    return null;
                },
            ],

            // -----------------------------------------------------------------
            // Aquatic Acute — H400
            // Uses M-factor weighted sum (simplified: assume M=1 unless overridden)
            // Threshold: sum of (% / 25) >= 1   →  i.e. any ingredient >= 25%
            // Full additive formula: Σ(Ci / L(E)C50i) ≥ 1
            // Simplified here as threshold-based
            // -----------------------------------------------------------------
            'aquatic_acute' => [
                'evaluate' => function (array $ingredients): ?array {
                    // Additive method: sum concentration/threshold for all H400 ingredients
                    // CLP cut-off for Aquatic Acute 1 = 25% (without M-factor)
                    $sum = 0.0;
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H400') {
                            $sum += $i['percentage'] / 25.0; // simplified — M=1
                        }
                    }
                    if ($sum >= 1.0) {
                        return [
                            'h_code'    => 'H400',
                            'category'  => '1',
                            'signal'    => 'Warning',
                            'pictogram' => 'environment',
                        ];
                    }
                    return null;
                },
            ],

            // -----------------------------------------------------------------
            // Aquatic Chronic — H410 / H411 / H412
            // H410 (Chronic 1): threshold 0.1% (M=1)
            // H411 (Chronic 2): threshold 1%
            // H412 (Chronic 3): threshold 10%
            // -----------------------------------------------------------------
            'aquatic_chronic' => [
                'evaluate' => function (array $ingredients): ?array {
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H410' && $i['percentage'] >= 0.1) {
                            return [
                                'h_code'    => 'H410',
                                'category'  => '1',
                                'signal'    => 'Warning',
                                'pictogram' => 'environment',
                            ];
                        }
                    }
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H411' && $i['percentage'] >= 1.0) {
                            return [
                                'h_code'    => 'H411',
                                'category'  => '2',
                                'signal'    => null,
                                'pictogram' => 'environment',
                            ];
                        }
                    }
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H412' && $i['percentage'] >= 10.0) {
                            return [
                                'h_code'    => 'H412',
                                'category'  => '3',
                                'signal'    => null,
                                'pictogram' => null,
                            ];
                        }
                    }
                    return null;
                },
            ],

            // -----------------------------------------------------------------
            // Flammable Liquid — H226 / H225 / H224
            // Threshold: 10% for Cat 3 (H226)
            // -----------------------------------------------------------------
            'flammable_liquid' => [
                'evaluate' => function (array $ingredients): ?array {
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H224' && $i['percentage'] >= 10.0) {
                            return ['h_code' => 'H224', 'category' => '1', 'signal' => 'Danger',  'pictogram' => 'flame'];
                        }
                    }
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H225' && $i['percentage'] >= 10.0) {
                            return ['h_code' => 'H225', 'category' => '2', 'signal' => 'Danger',  'pictogram' => 'flame'];
                        }
                    }
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H226' && $i['percentage'] >= 10.0) {
                            return ['h_code' => 'H226', 'category' => '3', 'signal' => 'Warning', 'pictogram' => 'flame'];
                        }
                    }
                    return null;
                },
            ],

            // -----------------------------------------------------------------
            // Reproductive Toxicity — H361 / H360
            // Threshold: Cat 2 (H361) = 3%, Cat 1 (H360) = 0.3%
            // -----------------------------------------------------------------
            'reproductive_toxicity' => [
                'evaluate' => function (array $ingredients): ?array {
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H360' && $i['percentage'] >= 0.3) {
                            return ['h_code' => 'H360', 'category' => '1', 'signal' => 'Danger',  'pictogram' => 'health-hazard'];
                        }
                    }
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H361' && $i['percentage'] >= 3.0) {
                            return ['h_code' => 'H361', 'category' => '2', 'signal' => 'Warning', 'pictogram' => 'health-hazard'];
                        }
                    }
                    return null;
                },
            ],

            // -----------------------------------------------------------------
            // Acute Oral Toxicity — H302 (Cat 4)
            // Threshold: 25%
            // -----------------------------------------------------------------
            'acute_toxicity_oral' => [
                'evaluate' => function (array $ingredients): ?array {
                    foreach ($ingredients as $i) {
                        if ($i['code'] === 'H302' && $i['percentage'] >= 25.0) {
                            return ['h_code' => 'H302', 'category' => '4', 'signal' => 'Warning', 'pictogram' => 'exclamation'];
                        }
                    }
                    return null;
                },
            ],

        ];
    }

    // -------------------------------------------------------------------------
    // Derive H statements from triggered classes
    // Returns: [ 'H317' => 'May cause an allergic skin reaction.', ... ]
    // -------------------------------------------------------------------------
    protected function deriveHStatements(array $triggeredClasses): array
    {
        $statements = [];

        foreach ($triggeredClasses as $classData) {
            $code = $classData['h_code'];
            $statements[$code] = $this->hStatementText($code);
        }

        // Sort by H-code number for consistent label output
        ksort($statements);

        return $statements;
    }

    // -------------------------------------------------------------------------
    // H statement text — official CLP wording
    // -------------------------------------------------------------------------
    protected function hStatementText(string $code): string
    {
        return match($code) {
            'H224' => 'Extremely flammable liquid and vapour.',
            'H225' => 'Highly flammable liquid and vapour.',
            'H226' => 'Flammable liquid and vapour.',
            'H302' => 'Harmful if swallowed.',
            'H304' => 'May be fatal if swallowed and enters airways.',
            'H312' => 'Harmful in contact with skin.',
            'H314' => 'Causes severe skin burns and eye damage.',
            'H315' => 'Causes skin irritation.',
            'H317' => 'May cause an allergic skin reaction.',
            'H318' => 'Causes serious eye damage.',
            'H319' => 'Causes serious eye irritation.',
            'H332' => 'Harmful if inhaled.',
            'H335' => 'May cause respiratory irritation.',
            'H336' => 'May cause drowsiness or dizziness.',
            'H360' => 'May damage fertility or the unborn child.',
            'H361' => 'Suspected of damaging fertility or the unborn child.',
            'H372' => 'Causes damage to organs through prolonged or repeated exposure.',
            'H373' => 'May cause damage to organs through prolonged or repeated exposure.',
            'H400' => 'Very toxic to aquatic life.',
            'H410' => 'Very toxic to aquatic life with long lasting effects.',
            'H411' => 'Toxic to aquatic life with long lasting effects.',
            'H412' => 'Harmful to aquatic life with long lasting effects.',
            default => $code,
        };
    }

    // -------------------------------------------------------------------------
    // Derive P statements from the triggered H codes
    // Based on CLP Annex IV, consumer-appropriate, minimal set
    // Returns: [ 'P102' => 'Keep out of reach of children.', ... ]
    // -------------------------------------------------------------------------
    protected function derivePStatements(array $hCodes): array
    {
        $pMap = $this->pStatementRules();
        $selected = [];

        // P102 is always included on consumer products
        $selected['P102'] = $pMap['P102'];

        foreach ($hCodes as $hCode) {
            $rules = $pMap['by_h_code'][$hCode] ?? [];
            foreach ($rules as $pCode) {
                if (isset($pMap[$pCode]) && !isset($selected[$pCode])) {
                    $selected[$pCode] = $pMap[$pCode];
                }
            }
        }

        ksort($selected);

        return $selected;
    }

    // -------------------------------------------------------------------------
    // P statement rules and text
    // Maps H codes → relevant P codes, and P codes → official text
    // -------------------------------------------------------------------------
    protected function pStatementRules(): array
    {
        return [
            // P statement text
            'P101'       => 'If medical advice is needed, have product container or label at hand.',
            'P102'       => 'Keep out of reach of children.',
            'P210'       => 'Keep away from heat, hot surfaces, sparks, open flames and other ignition sources. No smoking.',
            'P233'       => 'Keep container tightly closed.',
            'P260'       => 'Do not breathe vapours.',
            'P261'       => 'Avoid breathing vapours.',
            'P264'       => 'Wash hands thoroughly after handling.',
            'P270'       => 'Do not eat, drink or smoke when using this product.',
            'P271'       => 'Use only outdoors or in a well-ventilated area.',
            'P272'       => 'Contaminated work clothing should not be allowed out of the workplace.',
            'P273'       => 'Avoid release to the environment.',
            'P280'       => 'Wear protective gloves and eye protection.',
            'P301+P310'  => 'IF SWALLOWED: Immediately call a POISON CENTRE or doctor.',
            'P301+P312'  => 'IF SWALLOWED: Call a POISON CENTRE or doctor if you feel unwell.',
            'P302+P352'  => 'IF ON SKIN: Wash with plenty of water.',
            'P303+P361+P353' => 'IF ON SKIN (or hair): Take off immediately all contaminated clothing. Rinse skin with water.',
            'P304+P340'  => 'IF INHALED: Remove person to fresh air and keep comfortable for breathing.',
            'P305+P351+P338' => 'IF IN EYES: Rinse cautiously with water for several minutes. Remove contact lenses if present and easy to do. Continue rinsing.',
            'P308+P313'  => 'IF exposed or concerned: Get medical advice.',
            'P312'       => 'Call a POISON CENTRE or doctor if you feel unwell.',
            'P321'       => 'Specific treatment (see supplementary information on this label).',
            'P330'       => 'Rinse mouth.',
            'P331'       => 'Do NOT induce vomiting.',
            'P333+P313'  => 'If skin irritation or rash occurs: Get medical advice.',
            'P337+P313'  => 'If eye irritation persists: Get medical advice.',
            'P362+P364'  => 'Take off contaminated clothing and wash it before reuse.',
            'P370+P378'  => 'In case of fire: Use dry sand, dry chemical or alcohol-resistant foam to extinguish.',
            'P391'       => 'Collect spillage.',
            'P403+P235'  => 'Store in a well-ventilated place. Keep cool.',
            'P405'       => 'Store locked up.',
            'P501'       => 'Dispose of contents and container in accordance with local regulations.',

            // H code → P code mapping
            'by_h_code' => [
                'H224' => ['P210', 'P233', 'P303+P361+P353', 'P370+P378', 'P403+P235', 'P501'],
                'H225' => ['P210', 'P233', 'P303+P361+P353', 'P370+P378', 'P403+P235', 'P501'],
                'H226' => ['P210', 'P233', 'P370+P378', 'P403+P235', 'P501'],
                'H302' => ['P270', 'P301+P312', 'P330', 'P501'],
                'H304' => ['P301+P310', 'P331', 'P405', 'P501'],
                'H312' => ['P280', 'P302+P352', 'P312', 'P501'],
                'H314' => ['P260', 'P280', 'P301+P330+P331', 'P305+P351+P338', 'P308+P313', 'P501'],
                'H315' => ['P264', 'P302+P352', 'P332+P313', 'P362+P364'],
                'H317' => ['P261', 'P272', 'P302+P352', 'P333+P313', 'P362+P364', 'P501'],
                'H318' => ['P280', 'P305+P351+P338', 'P310'],
                'H319' => ['P264', 'P305+P351+P338', 'P337+P313'],
                'H332' => ['P261', 'P271', 'P304+P340', 'P312'],
                'H335' => ['P261', 'P271', 'P304+P340'],
                'H336' => ['P261', 'P271', 'P304+P340'],
                'H360' => ['P201', 'P308+P313', 'P405', 'P501'],
                'H361' => ['P201', 'P308+P313', 'P405', 'P501'],
                'H372' => ['P260', 'P270', 'P314'],
                'H373' => ['P260', 'P314'],
                'H400' => ['P273', 'P391', 'P501'],
                'H410' => ['P273', 'P391', 'P501'],
                'H411' => ['P273', 'P391', 'P501'],
                'H412' => ['P273', 'P501'],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Resolve signal word — Danger takes priority over Warning
    // -------------------------------------------------------------------------
    protected function resolveSignalWord(array $triggeredClasses): ?string
    {
        $words = array_column($triggeredClasses, 'signal');
        $words = array_filter($words); // remove nulls

        if (in_array('Danger', $words)) {
            return 'Danger';
        }
        if (in_array('Warning', $words)) {
            return 'Warning';
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Resolve pictograms — deduplicated, ordered
    // -------------------------------------------------------------------------
    protected function resolvePictograms(array $triggeredClasses): array
    {
        $pictograms = array_filter(array_unique(array_column($triggeredClasses, 'pictogram')));
        return array_values($pictograms);
    }
}
