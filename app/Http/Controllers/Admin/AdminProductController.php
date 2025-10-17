<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

use App\Models\Product;
use App\Models\Category;
use App\Models\Product\Seo;

class AdminProductController extends Controller
{
    /**
     * Display a listing of the resource. (Index)
     */
    public function index()
    {
        $products = Product::with('images:product_id,image')
            ->select('id', 'mpn', 'name', 'cost', 'stock_qty', 'status', 'parent_product_id')
            ->whereNull('parent_product_id') // Only show top-level products
            ->paginate(15);

        return Inertia::render('admin/product/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new resource. (Create)
     */
    public function create()
    {
        $categories = Category::select('id', 'name')->get();
        $parentProducts = Product::select('id', 'name')->get();

        return Inertia::render('admin/product/CreateEdit', [
            'categories' => $categories,
            'parentProducts' => $parentProducts,
            'isEditing' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage. (Store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mpn' => ['required', 'string', 'max:50', Rule::unique('product', 'mpn')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['required', Rule::in(['enabled', 'disabled'])],
            'cost' => ['required', 'numeric', 'min:0.01'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'category_ids' => ['array'],
            'category_ids.*' => ['exists:category,id'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('product_seo', 'slug')],
            // 'images' => ['array', 'max:5'], // For file uploads (not implemented here)
            'parent_product_id' => ['nullable', 'exists:product,id'],
        ]);

        return DB::transaction(function () use ($validated) {
            $product = Product::create($validated);

            // Sync Categories
            if (!empty($validated['category_ids'])) {
                $product->categories()->sync($validated['category_ids']);
            }

            // Create SEO Record
            $product->seo()->create([
                'meta_title' => $validated['meta_title'] ?? $validated['name'],
                'meta_description' => $validated['meta_description'] ?? substr(strip_tags($validated['description']), 0, 160),
                'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            ]);

            return redirect()->route('admin.products.index')
                ->with('success', "Product '{$product->name}' created successfully!");
        });
    }

    /**
     * Show the form for editing the specified resource. (Edit)
     */
    public function edit(Product $product)
    {
        $product->load(['seo:product_id,meta_title,meta_description,slug', 'categories:id']);
        $categories = Category::select('id', 'name')->get();

        $parentProducts = Product::where('id', '!=', $product->id)->select('id', 'name')->get();

        return Inertia::render('admin/product/CreateEdit', [
            'product' => $product,
            'categories' => $categories,
            'parentProducts' => $parentProducts,
            'selectedCategoryIds' => $product->categories->pluck('id'),
            'isEditing' => true,
        ]);
    }

    /**
     * Update the specified resource in storage. (Update)
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'mpn' => ['required', 'string', 'max:50', Rule::unique('product', 'mpn')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['required', Rule::in(['enabled', 'disabled'])],
            'cost' => ['required', 'numeric', 'min:0.01'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'category_ids' => ['array'],
            'category_ids.*' => ['exists:category,id'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('product_seo', 'slug')->ignore($product->seo->id ?? null, 'id')],
            'parent_product_id' => ['nullable', 'exists:product,id', Rule::notIn([$product->id])],
        ]);

        return DB::transaction(function () use ($validated, $product) {
            $product->update($validated);

            // Sync Categories
            $product->categories()->sync($validated['category_ids'] ?? []);

            // Update or create SEO Record
            $product->seo()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'meta_title' => $validated['meta_title'] ?? $validated['name'],
                    'meta_description' => $validated['meta_description'] ?? substr(strip_tags($validated['description']), 0, 160),
                    'slug' => $validated['slug'] ?? Str::slug($validated['name']),
                ]
            );

            return redirect()->route('admin.products.index')
                ->with('success', "Product '{$product->name}' updated successfully!");
        });
    }

    /**
     * Remove the specified resource from storage. (Destroy)
     */
    public function destroy(Product $product)
    {
        $productName = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$productName}' deleted successfully.");
    }

    public function relationshipIndex()
    {
        $products = Product::select('id', 'name', 'parent_product_id')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'parent_id' => $product->parent_product_id,
                ];
            });

        return Inertia::render('admin/product/Relationships', [
            'productsData' => $products,
        ]);
    }
}
