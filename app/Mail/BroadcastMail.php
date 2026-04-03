<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BroadcastMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public string $emailSubject;
    public string $emailBody;
    public string $recipientName;

    public function __construct(string $subject, string $body, string $recipientName)
    {
        $this->emailSubject   = $subject;
        $this->emailBody      = $body;
        $this->recipientName  = $recipientName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                config('mail.from.name', 'Chapter of You')
            ),
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.broadcast',
            with: [
                'subject'       => $this->emailSubject,
                'body'          => $this->emailBody,
                'recipientName' => $this->recipientName,
            ],
        );
    }
}
