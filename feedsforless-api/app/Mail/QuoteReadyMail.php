<?php

namespace App\Mail;

use App\Domains\Quotes\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public QuoteRequest $quoteRequest,
        public string $contactName,
        public string $quotesUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'FeedsForLess – Your Quote #' . $this->quoteRequest->id . ' Is Ready',
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-ready',
        );
    }
}
