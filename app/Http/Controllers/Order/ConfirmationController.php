<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConfirmationController extends Controller
{
    public function show(int $id)
    {
        $query = Order::with(['items.product'])->where('id', $id);

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            // Guests: verify via session token set during checkout
            $guestOrderId = session('guest_order_id');
            if ($guestOrderId !== $id) {
                abort(403);
            }
        }
        $order = $query->firstOrFail();

        $items = $order->items->map(fn ($item) => [
            'name'     => $item->product->name ?? 'Unknown Product',
            'quantity' => $item->quantity,
            'price'    => $item->product_cost,
            'total'    => $item->product_total,
        ]);

        return Inertia::render('order/Confirmation', [
            'order' => [
                'id'               => $order->id,
                'status'           => $order->status,
                'email'            => $order->email,
                'first_name'       => $order->first_name,
                'subtotal'         => $order->cost_total,
                'shipping'         => $order->shipping_total,
                'tax'              => $order->tax_total,
                'voucher_discount' => $order->voucher_discount ?? 0,
                'total'            => $order->grand_total,
                'items'            => $items,
                'shipping_address' => [
                    'line_1'   => $order->shipping_line_1,
                    'line_2'   => $order->shipping_line_2,
                    'city'     => $order->shipping_city,
                    'county'   => $order->shipping_county,
                    'postcode' => $order->shipping_postcode,
                    'country'  => $order->shipping_country,
                ],
                'created_at' => $order->created_at->format('d M Y, H:i'),
            ],
        ]);
    }
}
