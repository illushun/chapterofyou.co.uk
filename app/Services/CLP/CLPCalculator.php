<?php

namespace App\Services\CLP;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CLPCalculator
{
    public function calculate(Product $product)
    {
        $hazards = [];

        $materials = $product->materials()->with('oil.hazards')->get();

        foreach ($materials as $material) {

            foreach ($material->oil->hazards as $hazard) {

                if ($hazard->hazard_code === 'H317') {

                    if ($material->percentage >= 0.1) {
                        $hazards['H317'] = [
                            'signal_word' => 'Warning',
                            'pictogram' => 'exclamation'
                        ];
                    }
                }
            }
        }

        return $this->formatResult($hazards);
    }

    private function formatResult(array $hazards)
    {
        return [
            'signal_word' => count($hazards) ? 'Warning' : null,
            'required_pictograms' => collect($hazards)
                ->pluck('pictogram')
                ->unique()
                ->values(),
            'hazard_statements' => array_keys($hazards),
        ];
    }
}
