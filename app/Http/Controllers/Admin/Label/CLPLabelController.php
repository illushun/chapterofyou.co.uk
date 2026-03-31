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
            $hStatements = $saved->hazard_statements ?? [];
            $pStatements = $saved->precautionary_statements ?? [];
            $pictograms  = $saved->required_pictograms ?? [];
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

        // Embed pictograms as base64 so they survive the browser render
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

        // Return a plain blade view — no PDF library, the browser handles printing
        return response()->view('admin.clp-label-print', [
            'label'           => $label,
            'hStatements'     => $hStatements,
            'pStatements'     => $pStatements,
            'pictogramImages' => $pictogramImages,
        ]);
    }
}
