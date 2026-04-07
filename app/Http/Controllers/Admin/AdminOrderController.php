<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Order\Dispatched;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Models\Order;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user:id,name,email')
            ->select('id', 'user_id', 'first_name', 'last_name', 'email', 'grand_total', 'status', 'created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, email or order ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return Inertia::render('admin/order/Index', [
            'orders'  => $orders,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'user:id,name,email',
            'items.product:id,mpn,name,cost,stock_qty',
        ]);

        return Inertia::render('admin/order/Show', [
            'order'    => $order,
            'statuses' => self::STATUSES,
            'giftVoucherOrder' => $order->giftVoucherOrder()->with('voucher')->first(),
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status'       => ['required', 'in:' . implode(',', array_keys(self::STATUSES))],
            'tracking_url' => ['nullable', 'url'],
        ]);

        $previousStatus = $order->status;
        $order->update(['status' => $request->status]);

        return back()->with('success', "Order #{$order->id} status updated to {$request->status}.");
    }

    /**
     * Manually send the dispatch notification email.
     */
    public function sendDispatchEmail(Request $request, Order $order)
    {
        $request->validate([
            'tracking_url' => ['nullable', 'url'],
        ]);

        Mail::to($order->email)->send(
            new Dispatched($order, $request->tracking_url)
        );

        return back()->with('success', "Dispatch email sent to {$order->email}.");
    }

    /**
     * Resend the order confirmation email.
     */
    public function resendConfirmation(Order $order)
    {
        Mail::to($order->email)->send(new \App\Mail\Order\Confirmation($order));

        return back()->with('success', "Confirmation email resent to {$order->email}.");
    }

    public const STATUSES = [
        'not started' => 'Not Started',
        'processing' => 'Processing',
        'successful' => 'Successful',
        'cancelled'  => 'Cancelled',
        'failed'     => 'Failed',
        'refunded'   => 'Refunded',
    ];
}
