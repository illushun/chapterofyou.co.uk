<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminCartController extends Controller
{
    public function index(Request $request)
    {
        // Define active as either being associated with a user or not yet expired
        $activeCarts = Cart::with('user:id,name,email')
            ->select('id', 'user_id', 'session_id', 'expires_at', 'updated_at')
            ->withCount('items')
            ->where(function ($query) {
                // Carts associated with a user are always "active"
                $query->whereNotNull('user_id');
            })
            ->orWhere(function ($query) {
                // Guest carts are active if their expiry date is in the future
                $query->whereNotNull('session_id')
                    ->where('expires_at', '>', Carbon::now());
            })
            ->orderByDesc('updated_at')
            ->paginate(15);

        return Inertia::render('admin/cart/Index', [
            'carts' => $activeCarts,
        ]);
    }

    public function show(Cart $cart)
    {
        $cart->load(['user:id,name,email', 'items.product:id,mpn,name,cost']);

        return Inertia::render('admin/cart/Show', [
            'cart' => $cart,
        ]);
    }
}
