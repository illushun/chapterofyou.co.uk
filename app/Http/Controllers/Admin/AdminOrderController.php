<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $orders = Order::with('user:id,name,email')
            ->select('id', 'user_id', 'grand_total', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('admin/order/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['user:id,name,email', 'items.product:id,mpn,name,cost,stock_qty']);

        return Inertia::render('admin/order/Show', [
            'order' => $order,
        ]);
    }
}
