<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)
            ->where('status', 'enabled')
            ->firstOrFail();

        $products = $category->products()
            ->with(['images:product_id,image,status', 'seo:product_id,slug'])
            ->where('product.status', 'enabled')
            ->whereNull('product.parent_product_id')
            ->orderBy('product.name')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('category/Show', [
            'category' => [
                'id'               => $category->id,
                'name'             => $category->name,
                'slug'             => $category->slug,
                'description'      => $category->description,
                'image_url'        => $category->image_url,
                'meta_title'       => $category->meta_title ?: "{$category->name} | Chapter of You",
                'meta_description' => $category->meta_description
                                        ?: "Shop my {$category->name} collection. Handcrafted luxury reed diffusers by Chapter of You.",
            ],
            'products' => $products->through(fn ($p) => [
                'id'        => $p->id,
                'name'      => $p->name,
                'cost'      => $p->cost,
                'stock_qty' => $p->stock_qty,
                'slug'      => $p->seo?->slug ?? $p->id,
                'image'     => $p->images->where('status', 'enabled')->first()?->image
                                ?? $p->images->first()?->image,
            ]),
        ]);
    }
}
