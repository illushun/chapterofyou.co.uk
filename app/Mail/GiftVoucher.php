<?php

namespace App\Mail;

use App\Models\GiftVoucherOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftVoucher extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly GiftVoucherOrder $giftVoucherOrder)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "A little something, just for you 🤍",
        );
    }

    public function content(): Content
    {
        $gvo     = $this->giftVoucherOrder->load(['voucher', 'order']);
        $voucher = $gvo->voucher;

        return new Content(
            view: 'mail.gift-voucher',
            with: [
                'recipientName'   => $gvo->recipient_name,
                'purchaserName'   => $gvo->order->first_name,
                'personalMessage' => $gvo->personal_message,
                'voucherCode'     => $voucher->code,
                'amount'          => $voucher->value,
                'validUntil'      => $voucher->valid_until->format('d F Y'),
                'shopUrl'         => url('/products'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
