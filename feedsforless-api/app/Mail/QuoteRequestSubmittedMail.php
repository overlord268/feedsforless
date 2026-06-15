<?php

namespace App\Mail;

use App\Domains\Quotes\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public QuoteRequest $quoteRequest,
        public string $contactName,
        public string $quotesUrl,
        public ?string $registerUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'FeedsForLess – Quote Request #' . $this->quoteRequest->id . ' Received',
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-request-submitted',
        );
    }
}
