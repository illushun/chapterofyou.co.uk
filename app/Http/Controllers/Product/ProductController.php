<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

use App\Models\Product;
use App\Models\Product\View as ProductView;
use App\Models\Category;

class ProductController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $perPage = 12;
        Log::info('Fetching all products,....');

        $filters = $request->only([
            'search', 'categories', 'min_price', 'max_price', 'sort', 'in_stock'
        ]);

        $products = Product::with('categories')
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

    public function showProduct(Request $request): \Inertia\Response
    {
        $ipAddress = request()->ip();

        ProductView::updateOrCreate(
            [
                'product_id' => $product->id,
                'ip_address' => $ipAddress
            ],
            ['views' => 1]
        );

        dd($request);
    }
}
