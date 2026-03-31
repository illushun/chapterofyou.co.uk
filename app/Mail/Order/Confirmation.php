<?php

namespace App\Mail\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Confirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly Order $order)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Confirmation #{$this->order->id} — Chapter of You",
        );
    }

    public function content(): Content
    {
        $order = $this->order->load('items.product');

        $items = $order->items->map(fn ($item) => [
            'name'     => $item->product->name ?? 'Unknown Product',
            'quantity' => $item->quantity,
            'price'    => $item->product_cost,
            'total'    => $item->product_total,
        ])->toArray();

        return new Content(
            view: 'mail.order.confirmation',
            with: [
                'orderId'         => $order->id,
                'firstName'       => $order->first_name,
                'items'           => $items,
                'subtotal'        => $order->cost_total,
                'shipping'        => $order->shipping_total,
                'tax'             => $order->tax_total,
                'voucherDiscount' => $order->voucher_discount ?? 0,
                'total'           => $order->grand_total,
                'shippingAddress' => [
                    'name'    => "{$order->first_name} {$order->last_name}",
                    'line1'   => $order->shipping_line_1,
                    'line2'   => $order->shipping_line_2,
                    'city'    => $order->shipping_city,
                    'state'   => $order->shipping_county,
                    'zip'     => $order->shipping_postcode,
                    'country' => $order->shipping_country,
                ],
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
