<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $orders = Auth::user()
            ->orders()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Order $o) => [
                'id'     => $o->id,
                'date'   => $o->created_at->toISOString(),
                'total'  => (float) $o->grand_total,
                'status' => $o->status,
            ]);

        return Inertia::render('account/Orders', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product:id,mpn,name,cost']);

        return Inertia::render('account/order/View', [
            'order' => $order,
        ]);
    }
}
