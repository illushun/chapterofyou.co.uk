<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatchSheet;
use App\Models\Order;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminBatchSheetController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────────

    public function index()
    {
        $sheets = BatchSheet::with([
                'order:id,status,created_at',
                'product:id,name,mpn',
                'createdBy:id,name',
            ])
            ->latest()
            ->paginate(20);

        return Inertia::render('admin/batch-sheet/Index', [
            'sheets' => $sheets,
        ]);
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        // Optionally pre-link to an order
        $order = null;
        if ($request->has('order_id')) {
            $order = Order::with('items.product:id,name,mpn')
                ->select('id', 'status', 'created_at', 'first_name', 'last_name')
                ->find($request->order_id);
        }

        $orders = Order::select('id', 'status', 'created_at', 'first_name', 'last_name')
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id'    => $o->id,
                'label' => "#{$o->id} — {$o->first_name} {$o->last_name} ({$o->created_at->format('d M Y')})",
            ]);

        $products = Product::select('id', 'name', 'mpn')
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get();

        // Auto-generate a batch number
        $nextId      = (BatchSheet::max('id') ?? 0) + 1;
        $batchNumber = 'COY-' . str_pad($nextId, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(2));

        return Inertia::render('admin/batch-sheet/CreateEdit', [
            'orders'      => $orders,
            'products'    => $products,
            'batchNumber' => $batchNumber,
            'linkedOrder' => $order,
            'isEditing'   => false,
        ]);
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $this->validateSheet($request);
        $validated['created_by'] = Auth::id();

        $sheet = BatchSheet::create($validated);

        return redirect()->route('admin.batch-sheets.show', $sheet)
            ->with('success', "Batch sheet {$sheet->batch_number} created.");
    }

    // ── Show ───────────────────────────────────────────────────────────────────

    public function show(BatchSheet $batchSheet)
    {
        $batchSheet->load(['order:id,status,created_at,first_name,last_name', 'product:id,name,mpn', 'createdBy:id,name']);

        return Inertia::render('admin/batch-sheet/Show', [
            'sheet' => $this->formatSheet($batchSheet),
        ]);
    }

    // ── Edit ───────────────────────────────────────────────────────────────────

    public function edit(BatchSheet $batchSheet)
    {
        $batchSheet->load(['order', 'product', 'createdBy']);

        $orders = Order::select('id', 'status', 'created_at', 'first_name', 'last_name')
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id'    => $o->id,
                'label' => "#{$o->id} — {$o->first_name} {$o->last_name} ({$o->created_at->format('d M Y')})",
            ]);

        $products = Product::select('id', 'name', 'mpn')
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/batch-sheet/CreateEdit', [
            'sheet'       => $this->formatSheet($batchSheet),
            'orders'      => $orders,
            'products'    => $products,
            'batchNumber' => $batchSheet->batch_number,
            'isEditing'   => true,
        ]);
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    public function update(Request $request, BatchSheet $batchSheet)
    {
        $validated = $this->validateSheet($request, $batchSheet);
        $batchSheet->update($validated);

        return redirect()->route('admin.batch-sheets.show', $batchSheet)
            ->with('success', "Batch sheet {$batchSheet->batch_number} updated.");
    }

    // ── Destroy ────────────────────────────────────────────────────────────────

    public function destroy(BatchSheet $batchSheet)
    {
        $number = $batchSheet->batch_number;
        $batchSheet->delete();

        return redirect()->route('admin.batch-sheets.index')
            ->with('success', "Batch sheet {$number} deleted.");
    }

    // ── PDF download ───────────────────────────────────────────────────────────

    public function pdf(BatchSheet $batchSheet)
    {
        $batchSheet->load(['order', 'product', 'createdBy']);

        $pdf = Pdf::loadView('admin.batch-sheet', [
            'sheet' => $batchSheet,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'BatchSheet-' . $batchSheet->batch_number . '.pdf';
        return $pdf->download($filename);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function formatSheet(BatchSheet $sheet): array
    {
        return [
            'id'                       => $sheet->id,
            'batch_number'             => $sheet->batch_number,
            'blend_name'               => $sheet->blend_name,
            'date_of_manufacture'      => $sheet->date_of_manufacture?->format('Y-m-d'),
            'produced_by'              => $sheet->produced_by,
            'bottle_size_ml'           => $sheet->bottle_size_ml,
            'total_units_produced'     => $sheet->total_units_produced,
            'ingredients'              => $sheet->ingredients ?? [],
            'ifra_certificate_checked' => $sheet->ifra_certificate_checked,
            'max_percent_allowed'      => $sheet->max_percent_allowed,
            'sds_hazards_noted'        => $sheet->sds_hazards_noted,
            'clp_label_prepared'       => $sheet->clp_label_prepared,
            'notes'                    => $sheet->notes,
            'order_id'                 => $sheet->order_id,
            'product_id'               => $sheet->product_id,
            'order'                    => $sheet->order ? [
                'id'    => $sheet->order->id,
                'label' => "#{$sheet->order->id} — {$sheet->order->first_name} {$sheet->order->last_name}",
            ] : null,
            'product'                  => $sheet->product ? [
                'id'   => $sheet->product->id,
                'name' => $sheet->product->name,
                'mpn'  => $sheet->product->mpn,
            ] : null,
            'created_by_name'          => $sheet->createdBy?->name,
            'created_at'               => $sheet->created_at?->format('d M Y, H:i'),
        ];
    }

    private function validateSheet(Request $request, ?BatchSheet $existing = null): array
    {
        return $request->validate([
            'order_id'                 => ['nullable', 'exists:order,id'],
            'product_id'               => ['nullable', 'exists:product,id'],
            'batch_number'             => ['required', 'string', 'max:50'],
            'blend_name'               => ['required', 'string', 'max:255'],
            'date_of_manufacture'      => ['required', 'date'],
            'produced_by'              => ['required', 'string', 'max:255'],
            'bottle_size_ml'           => ['required', 'integer', 'min:1'],
            'total_units_produced'     => ['required', 'string', 'max:100'],
            'ingredients'              => ['required', 'array', 'min:1'],
            'ingredients.*.ingredient' => ['required', 'string', 'max:255'],
            'ingredients.*.supplier'   => ['nullable', 'string', 'max:255'],
            'ingredients.*.lot_batch_no' => ['nullable', 'string', 'max:100'],
            'ingredients.*.percent_used' => ['nullable', 'string', 'max:20'],
            'ingredients.*.weight_g'   => ['nullable', 'string', 'max:20'],
            'ingredients.*.sds_ifra_ref' => ['nullable', 'string', 'max:255'],
            'ifra_certificate_checked' => ['boolean'],
            'max_percent_allowed'      => ['nullable', 'string', 'max:100'],
            'sds_hazards_noted'        => ['nullable', 'string', 'max:500'],
            'clp_label_prepared'       => ['boolean'],
            'notes'                    => ['nullable', 'string'],
        ]);
    }
}
