<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\JournalPost;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ]);
        $term = Str::lower(trim($validated['q']));
        $like = '%'.$term.'%';

        $products = Product::query()
            ->with(['seo:product_id,slug', 'images' => fn ($query) => $query->where('status', 'enabled')->orderBy('id')])
            ->where('status', 'enabled')
            ->where('stock_qty', '>', 0)
            ->whereNull('parent_product_id')
            ->where(fn ($query) => $query
                ->whereRaw('LOWER(name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(mpn) LIKE ?', [$like]))
            ->orderBy('name')
            ->limit(4)
            ->get()
            ->map(fn (Product $product) => [
                'type' => 'product',
                'title' => $product->name,
                'subtitle' => 'Product · £'.number_format((float) $product->cost, 2),
                'url' => $product->seo?->slug ? '/product/'.$product->seo->slug : '/product/'.$product->id,
                'image' => $product->images->first()?->image,
            ]);

        $categories = Category::query()
            ->where('status', 'enabled')
            ->whereRaw('LOWER(name) LIKE ?', [$like])
            ->orderBy('name')
            ->limit(3)
            ->get()
            ->map(fn (Category $category) => [
                'type' => 'category',
                'title' => $category->name,
                'subtitle' => 'Category',
                'url' => '/category/'.$category->slug,
                'image' => $category->image_url,
            ]);

        $posts = JournalPost::query()
            ->published()
            ->where(fn ($query) => $query
                ->whereRaw('LOWER(title) LIKE ?', [$like])
                ->orWhereRaw('LOWER(excerpt) LIKE ?', [$like])
                ->orWhereRaw('LOWER(tags) LIKE ?', [$like]))
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn (JournalPost $post) => [
                'type' => 'journal',
                'title' => $post->title,
                'subtitle' => 'Journal',
                'url' => '/journal/'.$post->slug,
                'image' => $post->cover_image ? asset('storage/'.$post->cover_image) : null,
            ]);

        return response()->json([
            'results' => $products->concat($categories)->concat($posts)->values(),
        ]);
    }
}
