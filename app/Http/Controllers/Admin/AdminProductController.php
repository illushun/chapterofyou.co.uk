<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Product\Courier as ProductCourier;
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
use App\Models\Oil;
use App\Models\Product\Material;
use App\Models\Product\Faq as ProductFaq;

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
    * Replace all product_material rows for this product with the submitted set.
    * Using deleteAndInsert rather than upsert to keep it simple and reliable.
    */
    private function syncMaterials(Product $product, array $materials): void
    {
        // Wipe existing rows for this product
        Material::where('product_id', $product->id)->delete();

        // Re-insert the submitted set, skipping any rows with no oil selected
        foreach ($materials as $material) {
            if (empty($material['oil_id']) || $material['oil_id'] == 0) {
                continue;
            }

            Material::create([
                'product_id' => $product->id,
                'oil_id'     => $material['oil_id'],
                'percentage' => round((float) $material['percentage'], 4),
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
            ->orderBy('name', 'ASC')
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
        $categories     = Category::select('id', 'name')->get();
        $couriers       = Courier::select(['id', 'name'])->where('status', 'enabled')->orderBy('type', 'ASC')->orderBy('id', 'DESC')->get();
        $parentProducts = Product::select('id', 'name')->get();
        $oils           = Oil::select('id', 'name', 'supplier', 'cas_primary')->orderBy('name')->get();

        return Inertia::render('admin/product/CreateEdit', [
            'categories'       => $categories,
            'couriers'         => $couriers,
            'parentProducts'   => $parentProducts,
            'oils'             => $oils,
            'productMaterials' => [],
            'isEditing'        => false,
            'productImages'    => [],
        ]);
    }

    /**
     * Store a newly created resource in storage. (Store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mpn'              => ['required', 'string', 'max:50', Rule::unique('product', 'mpn')],
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string'],
            'status'           => ['required', Rule::in(['enabled', 'disabled'])],
            'cost'             => ['required', 'numeric', 'min:0.01'],
            'stock_qty'        => ['required', 'integer', 'min:0'],
            'category_ids'     => ['array'],
            'category_ids.*'   => ['exists:category,id'],
            'courier_id.*'     => ['exists:courier,id'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'slug'             => ['nullable', 'string', 'max:255', Rule::unique('product_seo', 'slug')],
            'parent_product_id' => ['nullable', 'exists:product,id'],
            'new_images'       => ['nullable', 'array', 'max:5'],
            'new_images.*'     => ['image', 'max:2048', 'mimes:jpeg,png,webp'],

            // Oil formulation
            'materials'              => ['nullable', 'array'],
            'materials.*.oil_id'     => ['required_with:materials', 'exists:oil,id', 'distinct'],
            'materials.*.percentage' => ['required_with:materials', 'numeric', 'min:0.01', 'max:100'],


            'how_to_use' => ['nullable', 'string'],
            'faqs'                  => ['nullable', 'array'],
            'faqs.*.question'       => ['required_with:faqs', 'string', 'max:500'],
            'faqs.*.answer'         => ['required_with:faqs', 'string', 'max:2000'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $product = Product::create($validated);

            if (!empty($validated['category_ids'])) {
                $product->categories()->sync($validated['category_ids']);
            }

            $this->syncFaqs($product, $validated['faqs'] ?? []);

            $product->courier()->create([
                'product_id' => $product->id,
                'courier_id' => $validated['courier_id'] ?? null,
                'per_item'   => $validated['courier_per_item'] ?? 'no',
            ]);

            $product->seo()->create([
                'meta_title'       => $validated['meta_title'] ?? $validated['name'],
                'meta_description' => $validated['meta_description'] ?? substr(strip_tags($validated['description']), 0, 160),
                'slug'             => $validated['slug'] ?? Str::slug($validated['name']),
            ]);

            if ($request->hasFile('new_images')) {
                $this->handleImageUpload($product, $request->file('new_images'));
            }

            // Sync oil formulation
            $this->syncMaterials($product, $validated['materials'] ?? []);

            cache()->forget('sitemap.xml');

            return redirect()->route('admin.products.index')
                ->with('success', "Product '{$product->name}' created successfully!");
        });
    }

    /**
     * Show the form for editing the specified resource. (Edit)
     */
    public function edit(Product $product)
    {
        $product->load(['seo:product_id,meta_title,meta_description,slug', 'categories:id', 'courier']);

        $categories   = Category::select('id', 'name')->get();
        $parentProducts = Product::where('id', '!=', $product->id)->select('id', 'name')->get();
        $oils         = Oil::select('id', 'name', 'supplier', 'cas_primary')->orderBy('name')->get();
        $productFaqs = ProductFaq::where('product_id', $product->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['question', 'answer', 'sort_order'])
            ->toArray();

        $productImages = $product->images()
            ->select('id', 'image', 'status')
            ->get()
            ->map(function (ProductImage $image) {
                $image->file_path = $image->image;
                $image->is_enabled = $image->status === 'enabled';
                return $image;
            });

        $couriers = Courier::select(['id', 'name', 'type', 'cost'])
            ->where('status', 'enabled')
            ->orderBy('type', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();

        // Load existing oil links for this product
        $productMaterials = Material::where('product_id', $product->id)
            ->select('oil_id', 'percentage')
            ->get()
            ->map(fn ($m) => [
                'oil_id'     => $m->oil_id,
                'percentage' => number_format((float) $m->percentage, 2, '.', ''),
            ]);

        return Inertia::render('admin/product/CreateEdit', [
            'product'           => $product,
            'categories'        => $categories,
            'couriers'          => $couriers,
            'parentProducts'    => $parentProducts,
            'selectedCategoryIds' => $product->categories->pluck('id'),
            'selectedCourierId' => $product->courier?->courier_id,
            'courierPerItem'    => $product->courier?->per_item,
            'productImages'     => $productImages,
            'oils'              => $oils,
            'productMaterials'  => $productMaterials,
            'isEditing'         => true,
            'productFaqs' => $productFaqs,
        ]);
    }

    /**
     * Update the specified resource in storage. (Update)
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'mpn'              => ['required', 'string', 'max:50', Rule::unique('product', 'mpn')->ignore($product->id)],
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string'],
            'status'           => ['required', Rule::in(['enabled', 'disabled'])],
            'cost'             => ['required', 'numeric', 'min:0.01'],
            'stock_qty'        => ['required', 'integer', 'min:0'],
            'category_ids'     => ['array'],
            'category_ids.*'   => ['exists:category,id'],
            'courier_id'       => ['nullable', 'exists:courier,id'],
            'courier_per_item' => ['nullable', Rule::in(['yes', 'no'])],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'slug'             => ['nullable', 'string', 'max:255', Rule::unique('product_seo', 'slug')->ignore($product->seo->id ?? null, 'id')],
            'parent_product_id' => ['nullable', 'exists:product,id', Rule::notIn([$product->id])],
            'new_images'       => ['nullable', 'array', 'max:5'],
            'new_images.*'     => ['image', 'max:2048', 'mimes:jpeg,png,webp'],
            'images_to_delete' => ['nullable', 'array'],
            'images_to_delete.*' => ['exists:product_image,id'],
            'images_to_toggle' => ['nullable', 'array'],
            'images_to_toggle.*' => ['exists:product_image,id'],

            // Oil formulation
            'materials'              => ['nullable', 'array'],
            'materials.*.oil_id'     => ['required_with:materials', 'exists:oil,id', 'distinct'],
            'materials.*.percentage' => ['required_with:materials', 'numeric', 'min:0.01', 'max:100'],

            'how_to_use' => ['nullable', 'string'],
            'faqs'                  => ['nullable', 'array'],
            'faqs.*.question'       => ['required_with:faqs', 'string', 'max:500'],
            'faqs.*.answer'         => ['required_with:faqs', 'string', 'max:2000'],
        ]);

        return DB::transaction(function () use ($request, $validated, $product) {
            $product->update($validated);

            $product->categories()->sync($validated['category_ids'] ?? []);

            $this->syncFaqs($product, $validated['faqs'] ?? []);

            if ($validated['courier_id'] === null) {
                $product->courier()->delete();
            } else {
                $product->courier()->updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'courier_id' => $validated['courier_id'],
                        'per_item'   => $validated['courier_per_item'] ?? 'no',
                    ]
                );
            }

            $product->seo()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'meta_title'       => $validated['meta_title'] ?? $validated['name'],
                    'meta_description' => $validated['meta_description'] ?? substr(strip_tags($validated['description']), 0, 160),
                    'slug'             => $validated['slug'] ?? Str::slug($validated['name']),
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
                    $image->update(['status' => $image->status === 'enabled' ? 'disabled' : 'enabled']);
                }
            }

            if ($request->hasFile('new_images')) {
                $this->handleImageUpload($product, $request->file('new_images'));
            }

            // Sync oil formulation
            $this->syncMaterials($product, $validated['materials'] ?? []);

            cache()->forget('sitemap.xml');

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

        cache()->forget('sitemap.xml');

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$productName}' deleted successfully.");
    }

    public function relationshipIndex()
    {
        $products = Product::select('id', 'name', 'mpn', 'status', 'stock_qty', 'parent_product_id')
            ->get()
            ->map(fn ($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'mpn'        => $p->mpn,
                'status'     => $p->status,
                'stock_qty'  => $p->stock_qty,
                'parent_id'  => $p->parent_product_id,
            ]);

        return Inertia::render('admin/product/Relationships', [
            'productsData' => $products,
        ]);
    }

    public function assignRelationship(Request $request)
    {
        $request->validate([
            'parent_id' => ['required', 'exists:product,id'],
            'child_id'  => ['required', 'exists:product,id', 'different:parent_id'],
        ]);

        // Prevent circular reference — child cannot be an ancestor of the parent
        $parentId = $request->parent_id;
        $childId  = $request->child_id;

        // Walk up the parent chain to check for cycles
        $current = Product::find($parentId);
        while ($current && $current->parent_product_id) {
            if ($current->parent_product_id == $childId) {
                return back()->withErrors(['child_id' => 'This would create a circular relationship.']);
            }
            $current = Product::find($current->parent_product_id);
        }

        Product::where('id', $childId)->update(['parent_product_id' => $parentId]);

        return back()->with('success', 'Relationship assigned.');
    }

    public function removeRelationship(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:product,id'],
        ]);

        Product::where('id', $request->product_id)->update(['parent_product_id' => null]);

        return back()->with('success', 'Product removed from parent.');
    }

    /**
    * Replace all FAQ rows for this product with the submitted set.
    * Preserves sort_order based on submission order.
    */
    private function syncFaqs(Product $product, array $faqs): void
    {
        // Wipe existing rows
        ProductFaq::where('product_id', $product->id)->delete();

        foreach ($faqs as $index => $faq) {
            $question = trim($faq['question'] ?? '');
            $answer   = trim($faq['answer']   ?? '');

            // Skip entirely blank rows
            if ($question === '' && $answer === '') {
                continue;
            }

            ProductFaq::create([
                'product_id' => $product->id,
                'question'   => $question,
                'answer'     => $answer,
                'sort_order' => $index,
            ]);
        }
    }
}
