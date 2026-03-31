<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WishlistController extends Controller
{
    /**
     * Display the user's wishlist page.
     */
    public function index()
    {
        $items = Wishlist::where('user_id', Auth::id())
            ->with([
                'product' => function ($q) {
                    $q->with(['images:product_id,image', 'seo:product_id,slug'])
                      ->select('id', 'mpn', 'name', 'cost', 'stock_qty', 'status');
                }
            ])
            ->latest()
            ->get()
            ->filter(fn ($item) => $item->product !== null) // guard deleted products
            ->map(fn ($item) => [
                'wishlist_id' => $item->id,
                'product'     => [
                    'id'        => $item->product->id,
                    'name'      => $item->product->name,
                    'mpn'       => $item->product->mpn,
                    'cost'      => $item->product->cost,
                    'stock_qty' => $item->product->stock_qty,
                    'status'    => $item->product->status,
                    'images'    => $item->product->images,
                    'seo'       => $item->product->seo,
                ],
                'added_at' => $item->created_at->format('d M Y'),
            ])
            ->values();

        return Inertia::render('account/Wishlist', [
            'items' => $items,
        ]);
    }

    /**
     * Toggle a product in/out of the wishlist.
     * Returns JSON so it works with axios calls from the Vue pages.
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:product,id'],
        ]);

        if (!Auth::check()) {
            return response()->json(['error' => 'You must be logged in to use the wishlist.'], 401);
        }

        $userId    = Auth::id();
        $productId = $request->product_id;

        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'wishlisted' => false,
                'message'    => 'Removed from wishlist.',
            ]);
        }

        Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);

        return response()->json([
            'wishlisted' => true,
            'message'    => 'Added to wishlist.',
        ]);
    }

    /**
     * Remove a specific item from the wishlist by wishlist row ID.
     * Used from the wishlist page.
     */
    public function remove(int $id)
    {
        Wishlist::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', 'Item removed from wishlist.');
    }
}
