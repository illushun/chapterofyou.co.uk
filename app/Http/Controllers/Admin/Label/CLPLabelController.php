<?php

namespace App\Http\Controllers\Admin\Label;

use App\Http\Controllers\Controller;
use App\Services\CLP\CLPCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Label\CLP as CLPLabel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

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
    public function pdf(Product $product, CLPCalculator $calculator)
    {
        // Use saved label if it exists, otherwise calculate live
        $saved = $product->clpLabel;

        if ($saved) {
            $hStatements = $saved->hazard_statements ?? [];
            $pStatements = $saved->precautionary_statements ?? [];
            $signalWord  = $saved->signal_word;
            $pictograms  = $saved->required_pictograms ?? [];
            $suppInfo    = $saved->supplementary_info;

            // Build a simple object the blade template can use
            $label = $saved;
        } else {
            // Calculate on the fly
            $clp = $calculator->calculate($product);

            $hStatements = $clp['hazard_statements'];
            $pStatements = $clp['precautionary_statements'];
            $signalWord  = $clp['signal_word'];
            $pictograms  = $clp['required_pictograms'];
            $suppInfo    = null;

            // Build a temporary stdClass to pass to the view
            $label = (object) [
                'product_name'      => $product->name,
                'supplier_name'     => config('clp.supplier_name', ''),
                'supplier_address'  => config('clp.supplier_address', ''),
                'supplier_phone'    => config('clp.supplier_phone', ''),
                'signal_word'       => $signalWord,
                'nominal_quantity'  => null,
                'supplementary_info' => null,
            ];
        }

        // Resolve pictogram images as base64 data URIs
        // Store your GHS SVG/PNG files in public/images/clp/
        // e.g. public/images/clp/GHS07.png
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

            $filename = $pictogramMap[$picKey] . '.png';
            $path     = public_path("storage/images/Pictograms/{$filename}");

            if (file_exists($path)) {
                $imageData = base64_encode(file_get_contents($path));
                $pictogramImages[$picKey] = "data:image/png;base64,{$imageData}";
            }
        }

        $pdf = Pdf::loadView('admin.clp-label', [
            'label'           => $label,
            'hStatements'     => $hStatements,
            'pStatements'     => $pStatements,
            'pictogramImages' => $pictogramImages,
        ]);

        // Set paper to custom 76mm × 50mm landscape
        $pdf->setPaper([0, 0, 215.43, 141.73], 'landscape'); // points: 76mm × 50mm

        $filename = 'CLP-Label-' . str($product->name)->slug() . '.pdf';

        return $pdf->download($filename);
    }
}
