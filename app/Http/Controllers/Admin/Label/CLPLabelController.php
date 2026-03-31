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
    public function pdf(Product $product, CLPCalculator $calculator)
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

        // Embed pictograms as base64
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

        $html = view('admin.clp-label', [
            'label'           => $label,
            'hStatements'     => $hStatements,
            'pStatements'     => $pStatements,
            'pictogramImages' => $pictogramImages,
        ])->render();

        // format: [width, height] in mm — no orientation flag needed.
        // 76mm wide x 50mm tall is exactly what we want.
        $mpdf = new \Mpdf\Mpdf([
            'format'            => [76, 50],
            'margin_top'        => 0,
            'margin_bottom'     => 0,
            'margin_left'       => 0,
            'margin_right'      => 0,
            'margin_header'     => 0,
            'margin_footer'     => 0,
            'default_font_size' => 5,
            'default_font'      => 'dejavusans',
            'tempDir'           => sys_get_temp_dir() . '/mpdf',
        ]);

        $mpdf->SetDisplayMode('real');
        $mpdf->WriteHTML($html);

        $filename = 'CLP-Label-' . str($product->name)->slug() . '.pdf';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
