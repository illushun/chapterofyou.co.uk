<?php

namespace App\Mail\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Dispatched extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly ?string $trackingUrl = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your self-care is on its way 🤍 — #COY-{$this->order->id}",
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
            view: 'mail.order.dispatched',
            with: [
                'orderId'     => $order->id,
                'firstName'   => $order->first_name,
                'items'       => $items,
                'total'       => $order->grand_total,
                'trackingUrl' => $this->trackingUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
