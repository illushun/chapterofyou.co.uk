<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\Product\View as ProductView;
use App\Models\Category;

class ProductController extends Controller
{
    private const ALLOWED_IP = '82.18.187.157';

    public function index(Request $request): \Inertia\Response
    {
        $clientIp = $request->ip();
        // if the user isnt whitelisted
        if ($clientIp !== self::ALLOWED_IP) {
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }

        $perPage = 12;
        $filters = $request->only([
            'search', 'categories', 'min_price', 'max_price', 'sort', 'in_stock'
        ]);

        $products = Product
            ::with('categories')
            ->with('images')
            ->with('seo:product_id,slug')
            ->withCount('uniqueViews')
            ->filter($filters)
            ->when($request->get('sort'), function ($query, $sort) {
                [$column, $direction] = explode(',', $sort);
                $query->orderBy($column, $direction);
            })
            ->paginate($perPage)
            ->withQueryString()
            ->toArray();

        $categories = Category::select('id', 'name')
            ->where('status', 'enabled')
            ->get();

        return Inertia::render('product/View', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified product resource.
     *
     * @param  string  $idOrSlug
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function show(string $idOrSlug, Request $request)
    {
        $clientIp = $request->ip();
        // if the user isnt whitelisted
        if ($clientIp !== self::ALLOWED_IP) {
            return Inertia::render('Welcome', [
                'siteName' => 'Chapter of You',
            ]);
        }
        logger()->channel('product_view')->info("Fetching product at URL {$idOrSlug}");

        $product = Product::with([
                'images:product_id,image',
                'categories:category.id,category.name',
                'uniqueViews',
                // Check if the product has variations (children)
                'children' => function ($query) {
                    $query->select('id', 'parent_product_id', 'mpn', 'name', 'cost', 'stock_qty')
                        ->where('status', 'enabled')
                        ->where('stock_qty', '>', 0);
                }
            ])
            ->where('status', 'enabled')
            ->where(function ($query) use ($idOrSlug) {
                if (is_numeric($idOrSlug)) {
                    $query->where('id', $idOrSlug);
                }
                else {
                    $query->whereHas('seo', function ($q) use ($idOrSlug) {
                        $q->where('slug', $idOrSlug);
                    });
                }
            })
            ->firstOrFail();

        DB::transaction(function () use ($product, $request) {
            $ipAddress = $request->ip();
            $view = $product->uniqueViews()->firstOrNew([
                'ip_address' => $ipAddress,
            ]);
            $view->views = $view->views + 1;
            $view->save();
        });

        $parentProduct = null;
        if ($product->parent_product_id) {
             $parentProduct = Product::where('id', $product->parent_product_id)
                ->select('id', 'name', 'mpn', 'description')
                ->first();
        }

        $categoryIds = $product->categories->pluck('id');
        $relatedProducts = Product::with(['images:product_id,image'])
            ->where('id', '!=', $product->id)
            ->whereNull('parent_product_id') // Only show top-level products
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds);
            })
            ->where('status', 'enabled')
            ->take(4)
            ->get();

        return Inertia::render('product/Show', [
            'product' => $product->loadMissing('seo'),
            'parent' => $parentProduct,
            'related' => $relatedProducts,
        ]);
    }
}
