<?php

namespace App\Mail;

use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketInsightsWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterSubscription $subscription,
        public ?string $suggestedProductName,
        public ?string $suggestedProductUrl,
        public string $registerUrl,
        public string $catalogUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'FeedsForLess — You\'re subscribed to Market Insights',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.market-insights-welcome',
        );
    }
}
