<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistLaunchMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public string $discountCode;

    public function __construct(string $discountCode = 'CHAPTERONE')
    {
        $this->discountCode = $discountCode;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re in — Chapter of You is live ✦',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.waitlist-launch',
            with: [
                'discountCode' => $this->discountCode,
            ],
        );
    }
}
