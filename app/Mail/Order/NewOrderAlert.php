<?php

namespace App\Mail\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderAlert extends Mailable
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
            subject: "New Order #COY-{$this->order->id} — {$this->order->first_name} {$this->order->last_name}",
        );
    }

    public function content(): Content
    {
        $order = $this->order->load('items.product');

        $items = $order->items->map(fn ($item) => [
            'name'     => $item->product->name ?? 'Unknown Product',
            'quantity' => $item->quantity,
            'total'    => $item->product_total,
        ])->toArray();

        return new Content(
            view: 'mail.order.new-order-alert',
            with: [
                'orderId'         => $order->id,
                'firstName'       => $order->first_name,
                'lastName'        => $order->last_name,
                'email'           => $order->email,
                'telephone'       => $order->telephone,
                'items'           => $items,
                'subtotal'        => $order->cost_total,
                'shipping'        => $order->shipping_total,
                'tax'             => $order->tax_total,
                'voucherDiscount' => $order->voucher_discount ?? 0,
                'total'           => $order->grand_total,
                'shippingAddress' => [
                    'line_1'   => $order->shipping_line_1,
                    'line_2'   => $order->shipping_line_2,
                    'city'     => $order->shipping_city,
                    'county'   => $order->shipping_county,
                    'postcode' => $order->shipping_postcode,
                    'country'  => $order->shipping_country,
                ],
                'adminUrl' => url("/admin/orders/{$order->id}"),
                'placedAt' => $order->created_at->format('d M Y \a\t H:i'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
