<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class BroadcastMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public string $emailSubject;
    public string $emailBody;
    public string $recipientName;
    public string $unsubscribeUrl;

    public function __construct(string $subject, string $body, User $recipient)
    {
        $this->emailSubject   = $subject;
        $this->emailBody      = $body;
        $this->recipientName  = $recipient->name;

        // Signed URL — valid for 30 days, requires no login
        $this->unsubscribeUrl = URL::signedRoute(
            'unsubscribe.show',
            ['user' => $recipient->id],
            now()->addDays(30)
        );
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
                'subject'        => $this->emailSubject,
                'body'           => $this->emailBody,
                'recipientName'  => $this->recipientName,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }
}
