<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestQuoteInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $guestEmail,
        public string $contactName,
        public string $registerUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'FeedsForLess – Create Your Account',
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.guest-quote-invitation',
        );
    }
}
