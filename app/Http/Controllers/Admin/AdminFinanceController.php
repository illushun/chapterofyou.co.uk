<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance\CostItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminFinanceController extends Controller
{
    public const CATEGORIES = [
        'packaging'  => 'Packaging',
        'fragrance'  => 'Fragrance',
        'material'   => 'Material',
        'labour'     => 'Labour',
        'overhead'   => 'Overhead',
        'other'      => 'Other',
    ];

    // ──────────────────────────────────────────────────
    // Cost Items
    // ──────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $query = CostItem::query()->orderBy('category')->orderBy('name');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('supplier_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $items = $query->get();

        $stats = [
            'total_items'      => CostItem::count(),
            'total_spend'      => CostItem::sum('purchase_price'),
            'categories_used'  => CostItem::distinct('category')->count('category'),
        ];

        return Inertia::render('admin/finance/Index', [
            'items'      => $items,
            'stats'      => $stats,
            'categories' => self::CATEGORIES,
            'filters'    => $request->only(['search', 'category']),
        ]);
    }

    public function storeCostItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'category'       => ['required', 'string', 'in:' . implode(',', array_keys(self::CATEGORIES))],
            'supplier_name'  => ['nullable', 'string', 'max:255'],
            'supplier_url'   => ['nullable', 'url', 'max:500'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'purchase_qty'   => ['required', 'integer', 'min:1'],
            'notes'          => ['nullable', 'string'],
        ]);

        CostItem::create($data);

        return back()->with('success', "'{$data['name']}' added.");
    }

    public function updateCostItem(Request $request, CostItem $costItem): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'category'       => ['required', 'string', 'in:' . implode(',', array_keys(self::CATEGORIES))],
            'supplier_name'  => ['nullable', 'string', 'max:255'],
            'supplier_url'   => ['nullable', 'url', 'max:500'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'purchase_qty'   => ['required', 'integer', 'min:1'],
            'notes'          => ['nullable', 'string'],
        ]);

        $costItem->update($data);

        return back()->with('success', "'{$costItem->name}' updated.");
    }

    public function destroyCostItem(CostItem $costItem): RedirectResponse
    {
        $name = $costItem->name;
        $costItem->delete();

        return back()->with('success', "'{$name}' deleted.");
    }

    // ──────────────────────────────────────────────────
    // Product Cost Breakdowns
    // ──────────────────────────────────────────────────

    public function productCosts(Request $request): Response
    {
        $query = Product::with(['costItems'])
            ->select('id', 'mpn', 'name', 'cost', 'status')
            ->whereNull('parent_product_id')
            ->orderBy('name');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('mpn', 'like', "%{$s}%");
            });
        }

        if ($request->filled('filter')) {
            match ($request->filter) {
                'costed'   => $query->whereHas('costItems'),
                'uncosted' => $query->whereDoesntHave('costItems'),
                default    => null,
            };
        }

        $products = $query->paginate(25)->withQueryString();

        $products->getCollection()->transform(function ($product) {
            $totalCost = $product->costItems->sum(function ($item) {
                $unitCost = $item->purchase_qty > 0 ? $item->purchase_price / $item->purchase_qty : 0;
                return $unitCost * $item->pivot->qty_per_unit;
            });

            $product->total_cost = round($totalCost, 4);
            $product->margin     = $product->cost > 0
                ? round((($product->cost - $totalCost) / $product->cost) * 100, 1)
                : null;

            return $product;
        });

        return Inertia::render('admin/finance/ProductCosts', [
            'products' => $products,
            'filters'  => $request->only(['search', 'filter']),
        ]);
    }

    public function productCostDetail(Request $request, Product $product): Response
    {
        $product->load(['costItems', 'images:product_id,image,status']);

        $costItems = $product->costItems->map(function ($item) {
            $unitCost = $item->purchase_qty > 0 ? $item->purchase_price / $item->purchase_qty : 0;
            return [
                'id'            => $item->id,
                'name'          => $item->name,
                'category'      => $item->category,
                'supplier_name' => $item->supplier_name,
                'supplier_url'  => $item->supplier_url,
                'purchase_price'=> (float) $item->purchase_price,
                'purchase_qty'  => $item->purchase_qty,
                'unit_cost'     => round($unitCost, 4),
                'qty_per_unit'  => (float) $item->pivot->qty_per_unit,
                'contribution'  => round($unitCost * $item->pivot->qty_per_unit, 4),
            ];
        });

        $allItems = CostItem::orderBy('category')->orderBy('name')
            ->get(['id', 'name', 'category', 'purchase_price', 'purchase_qty']);

        $allItems->each(function ($item) {
            $item->unit_cost = $item->purchase_qty > 0
                ? round($item->purchase_price / $item->purchase_qty, 4)
                : 0;
        });

        return Inertia::render('admin/finance/ProductCostDetail', [
            'product'    => [
                'id'     => $product->id,
                'mpn'    => $product->mpn,
                'name'   => $product->name,
                'cost'   => (float) $product->cost,
                'status' => $product->status,
                'images' => $product->images,
            ],
            'costItems'  => $costItems,
            'allItems'   => $allItems,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function addProductCost(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'cost_item_id' => ['required', 'exists:finance_cost_item,id'],
            'qty_per_unit' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $product->costItems()->syncWithoutDetaching([
            $data['cost_item_id'] => ['qty_per_unit' => $data['qty_per_unit']],
        ]);

        return back()->with('success', 'Cost item added.');
    }

    public function updateProductCost(Request $request, Product $product, CostItem $costItem): RedirectResponse
    {
        $data = $request->validate([
            'qty_per_unit' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $product->costItems()->updateExistingPivot($costItem->id, [
            'qty_per_unit' => $data['qty_per_unit'],
        ]);

        return back()->with('success', 'Quantity updated.');
    }

    public function removeProductCost(Product $product, CostItem $costItem): RedirectResponse
    {
        $product->costItems()->detach($costItem->id);

        return back()->with('success', "'{$costItem->name}' removed from cost breakdown.");
    }
}
