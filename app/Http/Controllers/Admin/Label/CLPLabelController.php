<?php

namespace App\Http\Controllers\Admin\Label;

use App\Http\Controllers\Controller;
use App\Services\CLP\CLPCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Label\CLP as CLPLabel;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class CLPLabelController extends Controller
{
    public function index()
    {
        $products = Product::select('id', 'name')->orderBy('name')->get();
        return Inertia::render('admin/label/CLPLabel', compact('products'));
    }

    public function calculate(Product $product, CLPCalculator $calculator)
    {
        $result = $calculator->calculate($product);

        return response()->json([
            'product'    => ['id' => $product->id, 'name' => $product->name],
            'clp'        => $result,
        ]);
    }

    public function save(Request $request, Product $product, CLPCalculator $calculator)
    {
        $request->validate([
            'supplier_name'    => 'nullable|string',
            'supplier_address' => 'nullable|string',
            'supplier_phone'   => 'nullable|string',
        ]);

        $clp = $calculator->calculate($product);

        CLPLabel::updateOrCreate(
            ['product_id' => $product->id],
            [
                'product_name'             => $product->name,
                'supplier_name'            => $request->supplier_name,
                'supplier_address'         => $request->supplier_address,
                'supplier_phone'           => $request->supplier_phone,
                'signal_word'              => $clp['signal_word'],
                'required_pictograms'      => $clp['required_pictograms'],
                'hazard_statements'        => $clp['hazard_statements'],
                'precautionary_statements' => [],
                'ingredients_json'         => [],
            ]
        );

        return back()->with('success', 'Label saved.');
    }

    /**
    * Generate and download a print-ready PDF CLP label.
    *
    * Route: GET /admin/clp-labels/{product}/pdf
    * Name:  admin.clp-labels.pdf
    */
    public function print(Product $product, CLPCalculator $calculator)
    {
        $saved = $product->clpLabel;

        if ($saved) {
            $hStatements = $saved->hazard_statements        ?? [];
            $pStatements = $saved->precautionary_statements ?? [];
            $pictograms  = $saved->required_pictograms      ?? [];
            $label       = $saved;
        } else {
            $clp = $calculator->calculate($product);

            $hStatements = $clp['hazard_statements'];
            $pStatements = $clp['precautionary_statements'];
            $pictograms  = $clp['required_pictograms'];

            $label = (object) [
                'product_name'       => $product->name,
                'supplier_name'      => config('clp.supplier_name', ''),
                'supplier_address'   => config('clp.supplier_address', ''),
                'supplier_phone'     => config('clp.supplier_phone', ''),
                'signal_word'        => $clp['signal_word'],
                'nominal_quantity'   => null,
                'supplementary_info' => null,
            ];
        }

        // ── Allergens ──────────────────────────────────────────────────────────────
        // Pull allergen names from the product's oil components.
        // oil_components stores ingredients from the SDS Section 3.
        // We surface anything marked as an allergen or with a CAS that appears
        // on the EU allergen list (26 fragrance allergens under CLP/cosmetics regs).
        // For now we read supplementary_info on the saved label if set,
        // or pull directly from the product materials → oil components.
        $allergens = [];

        if ($saved && !empty($saved->supplementary_info)) {
            // If the admin has manually entered allergens in supplementary_info, use those
            // Expected format: "Contains: Linalool, Linalyl acetate" or just "Linalool, Linalyl acetate"
            $raw = preg_replace('/^contains:\s*/i', '', $saved->supplementary_info);
            $allergens = array_map('trim', explode(',', $raw));
        } else {
            // Auto-detect from oil components linked to this product via product_material
            $product->loadMissing(['materials.oil.components']);

            // EU 26 fragrance allergens by name (case-insensitive match)
            $euAllergenNames = [
                'linalool', 'linalyl acetate', 'limonene', 'citronellol', 'geraniol',
                'citral', 'eugenol', 'coumarin', 'isoeugenol', 'benzyl alcohol',
                'benzyl salicylate', 'benzyl benzoate', 'benzyl cinnamate', 'cinnamal',
                'cinnamyl alcohol', 'hydroxycitronellal', 'alpha-isomethyl ionone',
                'amyl cinnamal', 'amylcinnamyl alcohol', 'anise alcohol', 'benzaldehyde',
                'butylphenyl methylpropional', 'evernia furfuracea', 'evernia prunastri',
                'farnesol', 'hexyl cinnamal',
            ];

            $found = [];
            foreach ($product->materials as $material) {
                if (!$material->oil) {
                    continue;
                }
                foreach ($material->oil->components as $component) {
                    $componentName = strtolower(trim($component->name ?? ''));
                    foreach ($euAllergenNames as $allergen) {
                        if (str_contains($componentName, $allergen) && !in_array($component->name, $found)) {
                            $found[] = $component->name;
                        }
                    }
                }
            }
            $allergens = $found;
        }

        // ── Product meta line (size, type) ────────────────────────────────────────
        $productMeta = null;
        if ($label->nominal_quantity ?? null) {
            $productMeta = $label->nominal_quantity;
        }

        // ── Pictogram images as base64 ────────────────────────────────────────────
        $pictogramMap = [
            'exclamation'   => 'GHS07',
            'health-hazard' => 'GHS08',
            'environment'   => 'GHS09',
            'flame'         => 'GHS02',
            'skull'         => 'GHS06',
            'corrosion'     => 'GHS05',
            'oxidizer'      => 'GHS03',
            'gas-cylinder'  => 'GHS04',
            'explosion'     => 'GHS01',
        ];

        $pictogramImages = [];
        foreach ($pictograms as $picKey) {
            if (!isset($pictogramMap[$picKey])) {
                continue;
            }
            $path = public_path("storage/images/Pictograms/{$pictogramMap[$picKey]}.png");
            if (file_exists($path)) {
                $pictogramImages[$picKey] = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
            }
        }

        return response()->view('admin.clp-label-print', [
            'label'           => $label,
            'hStatements'     => $hStatements,
            'pStatements'     => $pStatements,
            'pictogramImages' => $pictogramImages,
            'allergens'       => $allergens,
            'productMeta'     => $productMeta,
            'supplierName'    => config('clp.supplier_name', ''),
        ]);
    }
}
