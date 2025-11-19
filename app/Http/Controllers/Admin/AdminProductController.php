<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\Product\Seo;
use App\Models\Product\Image as ProductImage;

class AdminProductController extends Controller
{
    /**
     * Helper to store uploaded images and create database records.
     */
    private function handleImageUpload(Product $product, array $files): void
    {
        foreach ($files as $file) {
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            // Store file in the 'product_images' directory within the 'public' disk
            $path = $file->storeAs('product_images', $fileName, 'public');

            $product->images()->create([
                'image' => Storage::url($path),
                'status' => 'enabled',
            ]);
        }
    }

    /**
     * Display a listing of the resource. (Index)
     */
    public function index()
    {
        $products = Product::with('images:product_id,image,status')
            ->select('id', 'mpn', 'name', 'cost', 'stock_qty', 'status', 'parent_product_id')
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
            'productImages' => [],
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
            'parent_product_id' => ['nullable', 'exists:product,id'],

            'new_images' => ['nullable', 'array', 'max:5'], // Max 5 images per upload
            'new_images.*' => ['image', 'max:2048', 'mimes:jpeg,png,webp'], // Max 2MB, common image types
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

            if ($request->hasFile('new_images')) {
                $this->handleImageUpload($product, $request->file('new_images'));
            }

            return redirect()->route('admin.products.index')
                ->with('success', "Product '{$product->name}' created successfully!");
        });
    }

    /**
     * Show the form for editing the specified resource. (Edit)
     */
    public function edit(Product $product)
    {
        $product->load(['seo:product_id,meta_title,meta_description,slug', 'categories:id', 'courier:courier_id,per_item']);
        $categories = Category::select('id', 'name')->get();

        $parentProducts = Product::where('id', '!=', $product->id)->select('id', 'name')->get();

        $productImages = $product->images()
            ->select('id', 'image', 'status')
            ->get()
            ->map(function (ProductImage $image) {
                $image->file_path = $image->image;
                $image->is_enabled = $image->status === 'enabled';
                return $image;
            });

        $couriers = Courier::select('*')->where('status', 'enabled')->orderBy('type', 'ASC')->orderBy('id', 'DESC')->get();

        return Inertia::render('admin/product/CreateEdit', [
            'product' => $product,
            'categories' => $categories,
            'couriers' => $couriers,
            'parentProducts' => $parentProducts,
            'selectedCategoryIds' => $product->categories->pluck('id'),
            'productImages' => $productImages,
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

            'new_images' => ['nullable', 'array', 'max:5'],
            'new_images.*' => ['image', 'max:2048', 'mimes:jpeg,png,webp'],

            'images_to_delete' => ['nullable', 'array'],
            'images_to_delete.*' => ['exists:product_image,id'],

            'images_to_toggle' => ['nullable', 'array'],
            'images_to_toggle.*' => ['exists:product_image,id'],
        ]);

        return DB::transaction(function () use ($request, $validated, $product) {
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

            if (!empty($validated['images_to_delete'])) {
                $imagesToDelete = ProductImage::whereIn('id', $validated['images_to_delete'])
                                              ->where('product_id', $product->id)
                                              ->get();

                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete($image->image);
                    $image->delete();
                }
            }

            if (!empty($validated['images_to_toggle'])) {
                $imagesToToggle = ProductImage::whereIn('id', $validated['images_to_toggle'])
                                              ->where('product_id', $product->id)
                                              ->get();

                foreach ($imagesToToggle as $image) {
                    $newStatus = $image->status === 'enabled' ? 'disabled' : 'enabled';
                    $image->update(['status' => $newStatus]);
                }
            }

            if ($request->hasFile('new_images')) {
                $this->handleImageUpload($product, $request->file('new_images'));
            }

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

        $product->images->each(function (ProductImage $image) {
            Storage::disk('public')->delete($image->image);
        });
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
