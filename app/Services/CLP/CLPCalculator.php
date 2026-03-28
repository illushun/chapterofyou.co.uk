<?php

namespace App\Services\CLP;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CLPCalculator
{
    /**
     * Calculate CLP for a finished product (mixture).
     *
     * Returns:
     * [
     *   'signal_word'          => 'Warning',
     *   'required_pictograms'  => ['exclamation'],
     *   'hazard_statements'    => ['H317' => 'May cause an allergic skin reaction.'],
     * ]
     */
    public function calculate(Product $product): array
    {
        // Eager load what we need
        $product->loadMissing(['oils.hazards', 'materials']);

        $triggeredHazards = [];
        $pictograms       = [];
        $signalWords      = [];

        foreach ($product->oils as $oil) {
            // pivot percentage for this oil in this product
            $percentage = (float) $oil->pivot->percentage;

            foreach ($oil->hazards as $hazard) {
                $rule = $this->getRule($hazard->hazard_code);

                if ($rule && $percentage >= $rule['threshold']) {
                    $code = $hazard->hazard_code;

                    if (!isset($triggeredHazards[$code])) {
                        $triggeredHazards[$code] = $this->hazardStatement($code);
                    }

                    if ($hazard->pictogram) {
                        $pictograms[] = $hazard->pictogram;
                    }

                    if ($hazard->signal_word) {
                        $signalWords[] = $hazard->signal_word;
                    }
                }
            }
        }

        $pictograms  = array_values(array_unique($pictograms));
        $signalWord  = $this->resolveSignalWord($signalWords);

        return [
            'signal_word'         => $signalWord,
            'required_pictograms' => $pictograms,
            'hazard_statements'   => $triggeredHazards,
        ];
    }

    // -------------------------------------------------------
    // Rules: threshold % below which hazard doesn't apply
    // Based on CLP Annex I cut-off values
    // -------------------------------------------------------
    protected function getRule(string $hazardCode): ?array
    {
        $rules = [
            'H317' => ['threshold' => 0.1],   // Skin Sensitisation Cat 1
            'H315' => ['threshold' => 1.0],   // Skin Irritation
            'H319' => ['threshold' => 1.0],   // Eye Irritation
            'H361' => ['threshold' => 0.3],   // Reproductive Tox Cat 2
            'H400' => ['threshold' => 0.25],  // Aquatic Acute
            'H410' => ['threshold' => 0.25],  // Aquatic Chronic
            'H226' => ['threshold' => 10.0],  // Flammable Liquid
            'H302' => ['threshold' => 25.0],  // Acute Tox Oral Cat 4
        ];

        return $rules[$hazardCode] ?? null;
    }

    // Danger takes priority over Warning
    protected function resolveSignalWord(array $words): ?string
    {
        if (in_array('Danger', $words)) {
            return 'Danger';
        }
        if (in_array('Warning', $words)) {
            return 'Warning';
        }
        return null;
    }

    protected function hazardStatement(string $code): string
    {
        $statements = [
            'H302' => 'Harmful if swallowed.',
            'H315' => 'Causes skin irritation.',
            'H317' => 'May cause an allergic skin reaction.',
            'H319' => 'Causes serious eye irritation.',
            'H361' => 'Suspected of damaging fertility or the unborn child.',
            'H400' => 'Very toxic to aquatic life.',
            'H410' => 'Very toxic to aquatic life with long lasting effects.',
            'H226' => 'Flammable liquid and vapour.',
        ];

        return $statements[$code] ?? $code;
    }
}
