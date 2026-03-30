<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminVoucherController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────────

    public function index()
    {
        $vouchers = Voucher::withCount('usages')
            ->with('products:id,name')
            ->latest()
            ->paginate(20);

        return Inertia::render('admin/voucher/Index', [
            'vouchers' => $vouchers,
        ]);
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    public function create()
    {
        $products = Product::select('id', 'name', 'mpn')
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/voucher/CreateEdit', [
            'products'  => $products,
            'isEditing' => false,
        ]);
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $this->validateVoucher($request);

        $voucher = Voucher::create($validated);

        if (!$validated['applies_to_all_products'] && !empty($validated['product_ids'])) {
            $voucher->products()->sync($validated['product_ids']);
        }

        return redirect()->route('admin.vouchers.index')
            ->with('success', "Voucher '{$voucher->code}' created successfully.");
    }

    // ── Edit ───────────────────────────────────────────────────────────────────

    public function edit(Voucher $voucher)
    {
        $voucher->load('products:id,name');

        $products = Product::select('id', 'name', 'mpn')
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/voucher/CreateEdit', [
            'voucher'              => $voucher,
            'selectedProductIds'   => $voucher->products->pluck('id'),
            'products'             => $products,
            'isEditing'            => true,
        ]);
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    public function update(Request $request, Voucher $voucher)
    {
        $validated = $this->validateVoucher($request, $voucher->id);

        $voucher->update($validated);

        if ($validated['applies_to_all_products']) {
            $voucher->products()->detach();
        } else {
            $voucher->products()->sync($validated['product_ids'] ?? []);
        }

        return redirect()->route('admin.vouchers.index')
            ->with('success', "Voucher '{$voucher->code}' updated successfully.");
    }

    // ── Destroy ────────────────────────────────────────────────────────────────

    public function destroy(Voucher $voucher)
    {
        $code = $voucher->code;
        $voucher->delete();

        return redirect()->route('admin.vouchers.index')
            ->with('success', "Voucher '{$code}' deleted.");
    }

    // ── Usage log ──────────────────────────────────────────────────────────────

    public function usage(Voucher $voucher)
    {
        $usages = VoucherUsage::where('voucher_id', $voucher->id)
            ->with(['user:id,name,email', 'order:id,status,grand_total,created_at'])
            ->latest()
            ->paginate(25);

        return Inertia::render('admin/voucher/Usage', [
            'voucher' => $voucher,
            'usages'  => $usages,
        ]);
    }

    // ── Generate code helper ───────────────────────────────────────────────────

    public function generateCode()
    {
        $code = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        return response()->json(['code' => $code]);
    }

    // ── Shared validation ──────────────────────────────────────────────────────

    private function validateVoucher(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'code'                    => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_\-]+$/i',
                                          Rule::unique('vouchers', 'code')->ignore($ignoreId)],
            'description'             => ['nullable', 'string', 'max:255'],
            'type'                    => ['required', Rule::in(['percentage', 'fixed'])],
            'value'                   => ['required', 'numeric', 'min:0.01',
                                          $request->type === 'percentage' ? 'max:100' : 'max:9999.99'],
            'minimum_order_value'     => ['nullable', 'numeric', 'min:0'],
            'applies_to_all_products' => ['boolean'],
            'product_ids'             => ['nullable', 'array'],
            'product_ids.*'           => ['exists:product,id'],
            'stackable'               => ['boolean'],
            'new_customers_only'      => ['boolean'],
            'single_use_per_user'     => ['boolean'],
            'max_uses'                => ['nullable', 'integer', 'min:1'],
            'valid_from'              => ['nullable', 'date'],
            'valid_until'             => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active'               => ['boolean'],
        ]);

        // Uppercase the code
        $validated['code'] = strtoupper($validated['code']);

        return $validated;
    }
}
