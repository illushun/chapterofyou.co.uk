<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Product\Review;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    private const ALLOWED_IPS = [
        '82.18.187.157', // kace
        '176.27.250.172', // stu
    ];

    private function validIp(Request $request): bool
    {
        return in_array($request->ip(), self::ALLOWED_IPS);
    }

    public function index(Request $request): \Inertia\Response
    {

        $perPage = 12;
        $filters = $request->only(['search', 'min_price', 'max_price', 'sort', 'in_stock']);
        $categories = Category::select('id', 'name', 'slug')
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                $category->filter_slug = $category->slug ?: Str::slug($category->name).'-'.$category->id;

                return $category;
            });
        $requestedCategories = $request->input('category', $request->input('categories', []));
        $requestedCategories = is_array($requestedCategories)
            ? $requestedCategories
            : explode(',', (string) $requestedCategories);
        $filters['categories'] = collect($requestedCategories)
            ->map(function ($value) use ($categories) {
                if (is_numeric($value)) {
                    return $categories->firstWhere('id', (int) $value)?->filter_slug;
                }

                return trim((string) $value);
            })
            ->filter(fn ($slug) => $categories->contains('filter_slug', $slug))
            ->unique()
            ->values()
            ->all();
        $sorts = [
            'name,asc' => ['name', 'asc'],
            'name,desc' => ['name', 'desc'],
            'cost,asc' => ['cost', 'asc'],
            'cost,desc' => ['cost', 'desc'],
        ];
        $filters['sort'] = array_key_exists($filters['sort'] ?? '', $sorts)
            ? $filters['sort']
            : 'name,asc';
        [$sortColumn, $sortDirection] = $sorts[$filters['sort']];

        $queryFilters = $filters;
        $queryFilters['category_ids'] = $categories
            ->whereIn('filter_slug', $filters['categories'])
            ->pluck('id')
            ->all();

        $products = Product::with('categories')
            ->with('images')
            ->with('reviews')
            ->with('seo:product_id,slug')
            ->withCount('uniqueViews')
            ->filter($queryFilters)
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage)
            ->withQueryString()
            ->toArray();

        return Inertia::render('product/View', [
            'products' => $products,
            'categories' => $categories->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->filter_slug,
            ]),
            'filters' => $filters,
            'wishlistedIds' => Auth::check()
                ? Wishlist::where('user_id', Auth::id())
                    ->pluck('product_id')
                    ->toArray()
                : [],
        ]);
    }

    /** @return \Inertia\Response */
    public function show(string $idOrSlug, Request $request)
    {

        logger()->channel('product_view')->info("Fetching product at URL {$idOrSlug}");

        $product = Product::with([
            'images:product_id,image',
            'categories:category.id,category.name,category.slug',
            'reviews.user:id,name',
            'uniqueViews',
            'faqs',
            'children' => function ($query) {
                $query->select('id', 'parent_product_id', 'mpn', 'name', 'cost', 'stock_qty')
                    ->where('status', 'enabled')
                    ->where('stock_qty', '>', 0);
            },
            'refills' => function ($query) {
                $query->select('product.id', 'product.name', 'product.cost', 'product.stock_qty')
                    ->where('status', 'enabled');
            },
        ])
            ->where('status', 'enabled')
            ->where(function ($query) use ($idOrSlug) {
                if (is_numeric($idOrSlug)) {
                    $query->where('id', $idOrSlug);
                } else {
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

        $canReview = false;
        if (Auth::check()) {
            $canReview = Auth::user()->hasPurchased($product->id);
        }

        $journalPosts = $product->journalPosts()
            ->published()
            ->select('journal_post.id', 'title', 'slug', 'excerpt', 'cover_image', 'published_at')
            ->latest('published_at')
            ->take(3)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => $p->excerpt,
                'cover_image' => $p->cover_image ? asset('storage/'.$p->cover_image) : null,
                'published_at' => $p->published_at->format('d M Y'),
                'reading_time' => $p->reading_time,
            ]);

        return Inertia::render('product/Show', [
            'product' => $product->loadMissing('seo'),
            'parent' => $parentProduct,
            'related' => $relatedProducts,
            'journalPosts' => $journalPosts,
            'canReview' => $canReview,
            'wishlisted' => Auth::check()
                ? Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists()
                : false,
            'wishlistedIds' => Auth::check()
                ? Wishlist::where('user_id', Auth::id())
                    ->whereIn('product_id', $relatedProducts->pluck('id'))
                    ->pluck('product_id')
                    ->toArray()
                : [],
        ]);
    }

    /** @return \Illuminate\Http\RedirectResponse */
    public function storeReview(Request $request, Product $product)
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'max:1000'],
            'images' => ['nullable', 'array', 'max:3'], // Max 3 images
            'images.*' => ['image', 'max:2048', 'mimes:jpeg,png,jpg'], // Max 2MB per image
        ]);

        if (! Auth::check() || ! Auth::user()->hasPurchased($product->id)) {
            return redirect()->back()->withErrors(['review' => 'You must be logged in and have purchased this product to leave a review.']);
        }

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $uploadedImages[] = 'https://chapterofyou.co.uk/storage/'.$path;
            }
        }

        $product->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'message' => $request->message,
            'review_images' => $uploadedImages,
            'status' => 'pending', // Reviews must be approved
        ]);

        return redirect()->back()->with('success', 'Your review has been submitted and is awaiting approval!');
    }

    /** @return \Illuminate\Http\RedirectResponse */
    public function destroyReview(Review $review)
    {
        if (! Auth::check() || $review->user_id !== Auth::id()) {
            return redirect()->back()->withErrors(['review_delete' => 'You are not authorised to delete this review.']);
        }

        // Delete images from storage
        if ($review->review_images) {
            foreach ($review->review_images as $imagePath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($imagePath);
            }
        }

        $review->delete();

        return redirect()->back()->with('success', 'Your review has been successfully deleted.');
    }
}
