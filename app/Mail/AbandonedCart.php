<?php

namespace App\Mail;

use App\Models\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCart extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly Cart $cart)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You left something behind 🤍 — Chapter of You',
        );
    }

    public function content(): Content
    {
        $items = $this->cart->items->map(fn ($item) => [
            'name'      => $item->product->name ?? 'Product',
            'cost'      => $item->product->cost ?? 0,
            'quantity'  => $item->quantity,
            'subtotal'  => ($item->product->cost ?? 0) * $item->quantity,
            'image'     => $item->product->images->first()?->image,
            'slug'      => $item->product->seo?->slug ?? $item->product_id,
        ])->toArray();

        $cartTotal = collect($items)->sum('subtotal');

        return new Content(
            view: 'mail.abandoned-cart',
            with: [
                'firstName'   => $this->cart->user?->name
                                    ? explode(' ', $this->cart->user->name)[0]
                                    : 'there',
                'items'       => $items,
                'cartTotal'   => $cartTotal,
                'checkoutUrl' => url('/checkout'),
                'cartUrl'     => url('/cart'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
