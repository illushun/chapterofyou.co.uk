<?php

namespace App\Services\CLP;

use App\Models\Product;

/**
 * CLP Mixture Classification Calculator
 *
 * Implements CLP Regulation (EC) No 1272/2008 mixture classification rules.
 *
 * Steps:
 *  1. Load all ingredients (oils + base) with their % in the final product
 *  2. For each ingredient, load its stored hazard codes from oil_hazards
 *  3. Apply CLP Annex I mixture classification thresholds (additive method where applicable)
 *  4. Derive H statements from triggered hazard classes
 *  5. Derive P statements (consumer-appropriate minimal set) from triggered H codes
 */
class CLPCalculator
{
    // -------------------------------------------------------------------------
    // Entry point
    // -------------------------------------------------------------------------
    public function calculate(Product $product): array
    {
        // Load relationships:
        // - materials: product_material pivot (ingredient_id, percentage, is_base)
        // - materials.oil: the Oil model with its hazards
        $product->loadMissing([
            'materials',
            'materials.oil',
            'materials.oil.hazards',
        ]);

        // Step 1 + 2: Build ingredient list with final % and hazard codes
        $ingredients = $this->buildIngredientList($product);

        // Step 3 + 4 + 5: Classify mixture
        $triggeredClasses = $this->classifyMixture($ingredients);

        // Step 6: Generate H statements
        $hStatements = $this->deriveHStatements($triggeredClasses);

        // Step 7: Generate P statements
        $pStatements = $this->derivePStatements(array_keys($hStatements));

        $signalWord = $this->resolveSignalWord($triggeredClasses);
        $pictograms = $this->resolvePictograms($triggeredClasses);

        return [
            'signal_word'              => $signalWord,
            'required_pictograms'      => $pictograms,
            'hazard_statements'        => $hStatements,
            'precautionary_statements' => $pStatements,
            'triggered_classes'        => $triggeredClasses,
            'ingredients_used'         => $ingredients, // useful for debugging
        ];
    }

    // -------------------------------------------------------------------------
    // Step 1 + 2: Build the ingredient hazard list
    //
    // Returns a flat list of every hazard contribution from every ingredient:
    //
    // [
    //   [ 'name' => 'Lavender EO', 'percentage' => 15.0, 'code' => 'H317', 'is_base' => false ],
    //   [ 'name' => 'Lavender EO', 'percentage' => 15.0, 'code' => 'H410', 'is_base' => false ],
    //   [ 'name' => 'Ecobase',     'percentage' => 80.0, 'code' => null,   'is_base' => true  ],
    // ]
    //
    // One row per hazard code per ingredient. Ingredients with no hazards
    // still appear with code = null (important for % accounting).
    // -------------------------------------------------------------------------
    protected function buildIngredientList(Product $product): array
    {
        $list = [];

        foreach ($product->materials as $material) {
            $oil        = $material->oil;
            $percentage = (float) $material->percentage;
            $isBase     = (bool)  ($material->is_base ?? false);
            $name       = $oil?->name ?? 'Unknown';

            if ($oil === null) {
                continue;
            }

            $hazards = $oil->hazards;

            if ($hazards->isEmpty()) {
                // Ingredient has no hazards — record it so % accounting is complete
                $list[] = [
                    'name'       => $name,
                    'percentage' => $percentage,
                    'code'       => null,
                    'signal'     => null,
                    'is_base'    => $isBase,
                ];
                continue;
            }

            foreach ($hazards as $hazard) {
                $list[] = [
                    'name'       => $name,
                    'percentage' => $percentage,
                    'code'       => $hazard->hazard_code,
                    'signal'     => $hazard->signal_word ?? null,
                    'is_base'    => $isBase,
                ];
            }
        }

        return $list;
    }

    // -------------------------------------------------------------------------
    // Helpers: sum the total % of all ingredients carrying a given hazard code
    //
    // This is the CLP additive method — we don't just check if one ingredient
    // hits a threshold, we sum all contributions with that code.
    // -------------------------------------------------------------------------
    protected function sumByCode(array $ingredients, string $code): float
    {
        return array_sum(array_map(
            fn ($i) => $i['code'] === $code ? $i['percentage'] : 0.0,
            $ingredients
        ));
    }

    // Helper: does any single ingredient have this code at or above threshold?
    protected function anyAbove(array $ingredients, string $code, float $threshold): bool
    {
        foreach ($ingredients as $i) {
            if ($i['code'] === $code && $i['percentage'] >= $threshold) {
                return true;
            }
        }
        return false;
    }

    // -------------------------------------------------------------------------
    // Step 3 + 4 + 5: Classify mixture
    //
    // For each hazard class, apply the CLP Annex I mixture rule.
    // Returns array keyed by class name, each with h_code/category/signal/pictogram.
    // -------------------------------------------------------------------------
    protected function classifyMixture(array $ingredients): array
    {
        $triggered = [];

        // -----------------------------------------------------------------
        // Skin Sensitisation — H317
        // CLP Annex I 3.4.3.3: cut-off 0.1% for Cat 1/1A/1B
        // Additive: sum of all H317 ingredients >= 0.1% triggers classification
        // -----------------------------------------------------------------
        $h317sum = $this->sumByCode($ingredients, 'H317');
        if ($h317sum >= 0.1) {
            $triggered['skin_sensitisation'] = [
                'h_code'    => 'H317',
                'category'  => '1',
                'signal'    => 'Warning',
                'pictogram' => 'exclamation',
                'sum'       => $h317sum,
            ];
        }

        // -----------------------------------------------------------------
        // Skin Irritation — H315
        // CLP Annex I 3.2.3.3: generic cut-off 10%
        // Additive method applies
        // -----------------------------------------------------------------
        $h315sum = $this->sumByCode($ingredients, 'H315');
        if ($h315sum >= 10.0) {
            $triggered['skin_irritation'] = [
                'h_code'    => 'H315',
                'category'  => '2',
                'signal'    => 'Warning',
                'pictogram' => 'exclamation',
                'sum'       => $h315sum,
            ];
        }

        // -----------------------------------------------------------------
        // Eye Irritation — H319
        // CLP Annex I 3.3.3.3: generic cut-off 10%
        // Additive method applies
        // -----------------------------------------------------------------
        $h319sum = $this->sumByCode($ingredients, 'H319');
        if ($h319sum >= 10.0) {
            $triggered['eye_irritation'] = [
                'h_code'    => 'H319',
                'category'  => '2',
                'signal'    => 'Warning',
                'pictogram' => 'exclamation',
                'sum'       => $h319sum,
            ];
        }

        // -----------------------------------------------------------------
        // Serious Eye Damage — H318
        // CLP Annex I 3.3.3.3: cut-off 10%
        // -----------------------------------------------------------------
        $h318sum = $this->sumByCode($ingredients, 'H318');
        if ($h318sum >= 10.0) {
            $triggered['serious_eye_damage'] = [
                'h_code'    => 'H318',
                'category'  => '1',
                'signal'    => 'Danger',
                'pictogram' => 'corrosion',
                'sum'       => $h318sum,
            ];
        }

        // -----------------------------------------------------------------
        // Aspiration Hazard — H304
        // CLP Annex I 3.10.3.3: cut-off 10% for individual ingredient
        // Note: does NOT use additive method — a SINGLE ingredient >= 10%
        // triggers it (because it's about viscosity/physical property)
        // -----------------------------------------------------------------
        if ($this->anyAbove($ingredients, 'H304', 10.0)) {
            $triggered['aspiration_hazard'] = [
                'h_code'    => 'H304',
                'category'  => '1',
                'signal'    => 'Danger',
                'pictogram' => 'health-hazard',
            ];
        }

        // -----------------------------------------------------------------
        // Flammable Liquid — H226 / H225 / H224
        // CLP Annex I 2.6.4.3: cut-off 10% (additive by flash point category)
        // Priority: most severe wins
        // -----------------------------------------------------------------
        $h224sum = $this->sumByCode($ingredients, 'H224');
        $h225sum = $this->sumByCode($ingredients, 'H225');
        $h226sum = $this->sumByCode($ingredients, 'H226');

        if ($h224sum >= 10.0) {
            $triggered['flammable_liquid'] = [
                'h_code'    => 'H224',
                'category'  => '1',
                'signal'    => 'Danger',
                'pictogram' => 'flame',
                'sum'       => $h224sum,
            ];
        } elseif ($h225sum >= 10.0) {
            $triggered['flammable_liquid'] = [
                'h_code'    => 'H225',
                'category'  => '2',
                'signal'    => 'Danger',
                'pictogram' => 'flame',
                'sum'       => $h225sum,
            ];
        } elseif ($h226sum >= 10.0) {
            $triggered['flammable_liquid'] = [
                'h_code'    => 'H226',
                'category'  => '3',
                'signal'    => 'Warning',
                'pictogram' => 'flame',
                'sum'       => $h226sum,
            ];
        }

        // -----------------------------------------------------------------
        // Aquatic Acute — H400
        // CLP Annex I 4.1.3.5.5 additive formula:
        //   Σ (Ci × Mi) / 25 >= 1
        // Where Mi = M-factor (default 1 if unknown)
        // For simplicity we use M=1 for all — conservative but safe
        // -----------------------------------------------------------------
        $h400weighted = 0.0;
        foreach ($ingredients as $i) {
            if ($i['code'] === 'H400') {
                $mFactor = 1; // would be read from hazard record if available
                $h400weighted += ($i['percentage'] * $mFactor) / 25.0;
            }
        }
        if ($h400weighted >= 1.0) {
            $triggered['aquatic_acute'] = [
                'h_code'    => 'H400',
                'category'  => '1',
                'signal'    => 'Warning',
                'pictogram' => 'environment',
                'sum'       => $h400weighted * 25, // back to % for reference
            ];
        }

        // -----------------------------------------------------------------
        // Aquatic Chronic — H410 / H411 / H412
        // CLP Annex I 4.1.3.5.5 — tiered additive method
        //
        // Chronic 1 (H410): Σ(Ci × Mi) / 0.1 >= 1  → i.e. sum >= 0.1% (M=1)
        // Chronic 2 (H411): Σ Ci >= 1%
        // Chronic 3 (H412): Σ Ci >= 10%
        //
        // Ingredients with H400/H410 also contribute to chronic via
        // their chronic toxicity factor. Simplified here.
        // -----------------------------------------------------------------
        $h410weighted = 0.0;
        $h411sum      = 0.0;
        $h412sum      = 0.0;

        foreach ($ingredients as $i) {
            if ($i['code'] === 'H410') {
                $mFactor = 1;
                $h410weighted += ($i['percentage'] * $mFactor) / 0.1;
            }
            if ($i['code'] === 'H411') {
                $h411sum += $i['percentage'];
            }
            if ($i['code'] === 'H412') {
                $h412sum += $i['percentage'];
            }
        }

        // Also: H400 ingredients (without chronic data) conservatively contribute
        // to Chronic 2 at their full %
        $h400sum = $this->sumByCode($ingredients, 'H400');

        if ($h410weighted >= 1.0) {
            $triggered['aquatic_chronic'] = [
                'h_code'    => 'H410',
                'category'  => '1',
                'signal'    => 'Warning',
                'pictogram' => 'environment',
            ];
        } elseif ($h411sum >= 1.0 || ($h400sum + $h411sum) >= 1.0) {
            $triggered['aquatic_chronic'] = [
                'h_code'    => 'H411',
                'category'  => '2',
                'signal'    => null,
                'pictogram' => 'environment',
            ];
        } elseif ($h412sum >= 10.0) {
            $triggered['aquatic_chronic'] = [
                'h_code'    => 'H412',
                'category'  => '3',
                'signal'    => null,
                'pictogram' => null,
            ];
        }

        // -----------------------------------------------------------------
        // Reproductive Toxicity — H360 / H361
        // CLP Annex I 3.7.3.3:
        //   Cat 1 (H360): cut-off 0.3%
        //   Cat 2 (H361): cut-off 3%
        // Additive method: sum contributions
        // -----------------------------------------------------------------
        $h360sum = $this->sumByCode($ingredients, 'H360');
        $h361sum = $this->sumByCode($ingredients, 'H361');

        if ($h360sum >= 0.3) {
            $triggered['reproductive_toxicity'] = [
                'h_code'    => 'H360',
                'category'  => '1',
                'signal'    => 'Danger',
                'pictogram' => 'health-hazard',
                'sum'       => $h360sum,
            ];
        } elseif ($h361sum >= 3.0) {
            $triggered['reproductive_toxicity'] = [
                'h_code'    => 'H361',
                'category'  => '2',
                'signal'    => 'Warning',
                'pictogram' => 'health-hazard',
                'sum'       => $h361sum,
            ];
        }

        // -----------------------------------------------------------------
        // Acute Oral Toxicity — H302 (Cat 4)
        // CLP Annex I 3.1.3.6: ATE mixture formula applies
        // Simplified: cut-off 25% for Cat 4
        // -----------------------------------------------------------------
        $h302sum = $this->sumByCode($ingredients, 'H302');
        if ($h302sum >= 25.0) {
            $triggered['acute_toxicity_oral'] = [
                'h_code'    => 'H302',
                'category'  => '4',
                'signal'    => 'Warning',
                'pictogram' => 'exclamation',
                'sum'       => $h302sum,
            ];
        }

        // -----------------------------------------------------------------
        // STOT Single Exposure — H336 (Cat 3, narcotic)
        // CLP Annex I 3.8.3.3: cut-off 20%
        // -----------------------------------------------------------------
        $h336sum = $this->sumByCode($ingredients, 'H336');
        if ($h336sum >= 20.0) {
            $triggered['stot_single'] = [
                'h_code'    => 'H336',
                'category'  => '3',
                'signal'    => 'Warning',
                'pictogram' => 'exclamation',
                'sum'       => $h336sum,
            ];
        }

        return $triggered;
    }

    // -------------------------------------------------------------------------
    // Step 6: Derive H statements
    // -------------------------------------------------------------------------
    protected function deriveHStatements(array $triggeredClasses): array
    {
        $statements = [];

        foreach ($triggeredClasses as $classData) {
            $code = $classData['h_code'];
            $statements[$code] = $this->hStatementText($code);
        }

        ksort($statements);
        return $statements;
    }

    protected function hStatementText(string $code): string
    {
        return match($code) {
            'H224' => 'Extremely flammable liquid and vapour.',
            'H225' => 'Highly flammable liquid and vapour.',
            'H226' => 'Flammable liquid and vapour.',
            'H302' => 'Harmful if swallowed.',
            'H304' => 'May be fatal if swallowed and enters airways.',
            'H315' => 'Causes skin irritation.',
            'H317' => 'May cause an allergic skin reaction.',
            'H318' => 'Causes serious eye damage.',
            'H319' => 'Causes serious eye irritation.',
            'H336' => 'May cause drowsiness or dizziness.',
            'H360' => 'May damage fertility or the unborn child.',
            'H361' => 'Suspected of damaging fertility or the unborn child.',
            'H400' => 'Very toxic to aquatic life.',
            'H410' => 'Very toxic to aquatic life with long lasting effects.',
            'H411' => 'Toxic to aquatic life with long lasting effects.',
            'H412' => 'Harmful to aquatic life with long lasting effects.',
            default => $code,
        };
    }

    // -------------------------------------------------------------------------
    // Step 7: Derive P statements — consumer product minimal set
    //
    // Based on CLP Annex IV. For consumer products (Article 22(d)):
    // workplace handling statements are omitted; only actionable
    // consumer-facing statements are included.
    //
    // Always:
    //   P102 — keep out of reach of children
    //   P501 — disposal (when any hazard triggered)
    //
    // By hazard:
    //   H317 → P333+P313
    //   H400/H410/H411 → P273, P391
    //   H412 → P273
    //   H304 → P301+P310, P331
    //   H224/H225/H226 → P210
    //   H360/H361 → P308+P313
    //   H318/H319 → P305+P351+P338
    //   H336 → P271
    // -------------------------------------------------------------------------
    protected function derivePStatements(array $hCodes): array
    {
        if (empty($hCodes)) {
            return [];
        }

        $allStatements = [
            'P102'           => 'Keep out of reach of children.',
            'P210'           => 'Keep away from heat, sparks and open flames. No smoking.',
            'P271'           => 'Use only outdoors or in a well-ventilated area.',
            'P273'           => 'Avoid release to the environment.',
            'P301+P310'      => 'IF SWALLOWED: Immediately call a POISON CENTRE or doctor.',
            'P305+P351+P338' => 'IF IN EYES: Rinse cautiously with water for several minutes. Remove contact lenses if present and easy to do. Continue rinsing.',
            'P308+P313'      => 'IF exposed or concerned: Get medical advice.',
            'P331'           => 'Do NOT induce vomiting.',
            'P333+P313'      => 'If skin irritation or rash occurs: Get medical advice.',
            'P391'           => 'Collect spillage.',
            'P501'           => 'Dispose of contents and container in accordance with local regulations.',
        ];

        $byHCode = [
            'H224' => ['P210', 'P501'],
            'H225' => ['P210', 'P501'],
            'H226' => ['P210', 'P501'],
            'H302' => ['P501'],
            'H304' => ['P301+P310', 'P331', 'P501'],
            'H315' => ['P501'],
            'H317' => ['P333+P313', 'P501'],
            'H318' => ['P305+P351+P338', 'P501'],
            'H319' => ['P305+P351+P338', 'P501'],
            'H336' => ['P271', 'P501'],
            'H360' => ['P308+P313', 'P501'],
            'H361' => ['P308+P313', 'P501'],
            'H400' => ['P273', 'P391', 'P501'],
            'H410' => ['P273', 'P391', 'P501'],
            'H411' => ['P273', 'P391', 'P501'],
            'H412' => ['P273', 'P501'],
        ];

        // Always include P102
        $selected = ['P102' => $allStatements['P102']];

        foreach ($hCodes as $hCode) {
            foreach ($byHCode[$hCode] ?? [] as $pCode) {
                if (isset($allStatements[$pCode]) && !isset($selected[$pCode])) {
                    $selected[$pCode] = $allStatements[$pCode];
                }
            }
        }

        ksort($selected);
        return $selected;
    }

    // -------------------------------------------------------------------------
    // Signal word — Danger takes priority over Warning
    // -------------------------------------------------------------------------
    protected function resolveSignalWord(array $triggeredClasses): ?string
    {
        $words = array_filter(array_column($triggeredClasses, 'signal'));

        if (in_array('Danger', $words)) {
            return 'Danger';
        }
        if (in_array('Warning', $words)) {
            return 'Warning';
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Pictograms — deduplicated list
    // -------------------------------------------------------------------------
    protected function resolvePictograms(array $triggeredClasses): array
    {
        return array_values(
            array_filter(
                array_unique(
                    array_column($triggeredClasses, 'pictogram')
                )
            )
        );
    }
}
