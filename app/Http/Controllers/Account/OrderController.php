<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Display a listing of the authenticated user's orders.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();

        $orders = $user->orders()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_id_display' => $order->payment_intent_id,
                    'date' => $order->created_at->format('Y-m-d H:i'),
                    'total' => (float) number_format($order->grand_total, 2, '.', ''),
                    'status' => ucfirst($order->status),
                ];
            });

        return Inertia::render('Account/Orders', [
            'orders' => $orders,
        ]);
    }
}
