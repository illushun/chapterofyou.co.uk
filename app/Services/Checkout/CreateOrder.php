<?php

namespace App\Services\Checkout;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Stripe\PaymentIntent;

class CreateOrder
{
    public function create(Cart $cart, array $summary, array $formData, PaymentIntent $paymentIntent, ?array $pendingGiftVoucher): Order
    {
        $names = explode(' ', $formData['fullName'], 2);
        $firstName = $names[0];
        $lastName = $names[1] ?? $names[0];

        $orderData = [
            'user_id' => Auth::id(),
            'payment_intent_id' => $paymentIntent->id,
            'payment_type' => $paymentIntent->payment_method_types[0] ?? $formData['paymentType'],

            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $formData['email'],
            'telephone' => $formData['telephone'] ?? null,

            'cost_total' => $summary['subtotal'],
            'shipping_total' => $summary['shipping'],
            'voucher_discount' => $summary['voucher_discount'],
            'tax_total' => $summary['vat_component'],
            'grand_total' => $summary['total'],

            'billing_line_1' => $formData['addressLine1'],
            'billing_line_2' => $formData['addressLine2'] ?? null,
            'billing_city' => $formData['city'],
            'billing_county' => $formData['county'] ?? null,
            'billing_postcode' => $formData['postcode'],
            'billing_country' => $formData['country'],
            'shipping_line_1' => $formData['addressLine1'],
            'shipping_line_2' => $formData['addressLine2'] ?? null,
            'shipping_city' => $formData['city'],
            'shipping_county' => $formData['county'] ?? null,
            'shipping_postcode' => $formData['postcode'],
            'shipping_country' => $formData['country'],

            'status' => 'successful',
        ];

        $order = Order::create($orderData);

        $orderItems = $cart->items->map(function ($item) use ($order) {
            $productCost = $item->product->cost ?? 0.00;

            return new OrderItem([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'product_cost' => $productCost,
                'product_total' => round($productCost * $item->quantity, 2),
            ]);
        });

        $order->items()->saveMany($orderItems);

        if ($pendingGiftVoucher) {

            $gvProduct = \App\Models\Product::where('mpn', 'GIFT-VOUCHER')->first();
            if ($gvProduct) {
                $order->items()->create([
                    'order_id' => $order->id,
                    'product_id' => $gvProduct->id,
                    'quantity' => 1,
                    'product_cost' => $pendingGiftVoucher['amount'],
                    'product_total' => $pendingGiftVoucher['amount'],
                ]);
            }

            app(\App\Services\GiftVoucherService::class)->createFromOrder(
                order: $order,
                amount: (float) $pendingGiftVoucher['amount'],
                deliveryType: $pendingGiftVoucher['delivery_type'],
                recipientName: $pendingGiftVoucher['recipient_name'],
                recipientEmail: $pendingGiftVoucher['recipient_email'] ?? null,
                personalMessage: $pendingGiftVoucher['personal_message'] ?? null,
                sendEmail: false,
            );

        }

        $cart->items()->delete();

        return $order;
    }
}
