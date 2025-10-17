<?php

namespace App\Http\Controllers\Admin\Label;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

use App\Models\Product;
use App\Models\Label\CLP as CLPLabel;

class CLPLabelController extends Controller
{
    /**
     * Display the CLP Label Generator page.
     * In a real application, you might pass Product data here.
     */
    public function index()
    {
        $clpLabels = CLPLabel::with('product')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('admin/label/CLPLabel', [
            'recentLabels' => $clpLabels,
            'products' => Product::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created CLP label in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'product_name' => ['required', 'string', 'max:255'],
            'concentration_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_address' => ['required', 'string'],
            'supplier_phone' => ['required', 'string', 'max:50'],

            // CLP Output fields (These come from the client-side calculation)
            'signal_word' => ['nullable', 'string', Rule::in(['Danger', 'Warning'])],
            'required_pictograms' => ['nullable', 'array'],
            'hazard_statements' => ['required', 'array'],
            'precautionary_statements' => ['required', 'array'],
            'supplementary_info' => ['nullable', 'string'],
            'ingredients_json' => ['nullable', 'array'],
        ]);

        CLPLabel::create($validated);
        return redirect()->back()->with('success', 'CLP Label successfully generated and saved!');
    }
}
