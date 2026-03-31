<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\User;
use Inertia\Inertia;

class AdminWishlistController extends Controller
{
    /**
     * Overview — all wishlisted products ranked by popularity.
     */
    public function index()
    {
        // Most wishlisted products
        $popularProducts = Wishlist::with('product:id,name,mpn,cost,stock_qty')
            ->selectRaw('product_id, COUNT(*) as wishlist_count')
            ->groupBy('product_id')
            ->orderByDesc('wishlist_count')
            ->get()
            ->filter(fn ($row) => $row->product !== null)
            ->map(fn ($row) => [
                'product_id'     => $row->product_id,
                'name'           => $row->product->name,
                'mpn'            => $row->product->mpn,
                'cost'           => $row->product->cost,
                'stock_qty'      => $row->product->stock_qty,
                'wishlist_count' => $row->wishlist_count,
            ])
            ->values();

        // All users who have at least one wishlist item
        $users = User::whereHas('wishlist')
            ->withCount('wishlist')
            ->orderByDesc('wishlist_count')
            ->get(['id', 'name', 'email'])
            ->map(fn ($u) => [
                'id'             => $u->id,
                'name'           => $u->name,
                'email'          => $u->email,
                'wishlist_count' => $u->wishlist_count,
            ]);

        $totalItems = Wishlist::count();
        $totalUsers = $users->count();

        return Inertia::render('admin/wishlist/Index', [
            'popularProducts' => $popularProducts,
            'users'           => $users,
            'totalItems'      => $totalItems,
            'totalUsers'      => $totalUsers,
        ]);
    }

    /**
     * Show a specific user's wishlist.
     */
    public function show(User $user)
    {
        $items = Wishlist::where('user_id', $user->id)
            ->with('product:id,name,mpn,cost,stock_qty')
            ->latest()
            ->get()
            ->filter(fn ($item) => $item->product !== null)
            ->map(fn ($item) => [
                'wishlist_id'    => $item->id,
                'product_id'     => $item->product->id,
                'name'           => $item->product->name,
                'mpn'            => $item->product->mpn,
                'cost'           => $item->product->cost,
                'stock_qty'      => $item->product->stock_qty,
                'added_at'       => $item->created_at->format('d M Y, H:i'),
            ])
            ->values();

        return Inertia::render('admin/wishlist/Show', [
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'items' => $items,
        ]);
    }
}
