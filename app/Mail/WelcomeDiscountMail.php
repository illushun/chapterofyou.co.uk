<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeDiscountMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public string $voucherCode) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your 10% off code — Chapter of You ✦',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.welcome-discount',
            with: ['voucherCode' => $this->voucherCode],
        );
    }
}
