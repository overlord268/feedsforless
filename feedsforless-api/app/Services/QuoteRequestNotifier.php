<?php

namespace App\Services;

use App\Domains\Quotes\Models\QuoteRequest;
use App\Mail\QuoteReadyMail;
use App\Mail\QuoteRejectedMail;
use App\Mail\QuoteRequestSubmittedMail;
use Exception;
use Illuminate\Support\Facades\Mail;

class QuoteRequestNotifier
{
    public function sendSubmitted(QuoteRequest $quoteRequest, ?string $registerUrl = null): void
    {
        $recipient = $this->recipient($quoteRequest);
        if (!$recipient) {
            return;
        }

        $this->send(
            $recipient['email'],
            new QuoteRequestSubmittedMail(
                quoteRequest: $quoteRequest,
                contactName: $recipient['name'],
                quotesUrl: $this->quotesUrl($quoteRequest),
                registerUrl: $registerUrl,
            )
        );
    }

    public function sendQuoted(QuoteRequest $quoteRequest): void
    {
        $recipient = $this->recipient($quoteRequest);
        if (!$recipient) {
            return;
        }

        $this->send(
            $recipient['email'],
            new QuoteReadyMail(
                quoteRequest: $quoteRequest,
                contactName: $recipient['name'],
                quotesUrl: $this->quotesUrl($quoteRequest),
            )
        );
    }

    public function sendRejected(QuoteRequest $quoteRequest, ?string $reason = null): void
    {
        $recipient = $this->recipient($quoteRequest);
        if (!$recipient) {
            return;
        }

        $this->send(
            $recipient['email'],
            new QuoteRejectedMail(
                quoteRequest: $quoteRequest,
                contactName: $recipient['name'],
                reason: $reason,
                quotesUrl: $this->quotesUrl($quoteRequest),
            )
        );
    }

    /**
     * @return array{email: string, name: string}|null
     */
    private function recipient(QuoteRequest $quoteRequest): ?array
    {
        $quoteRequest->loadMissing(['requester']);

        if ($quoteRequest->request_by_id && $quoteRequest->requester?->email) {
            $name = trim(($quoteRequest->requester->first_name ?? '') . ' ' . ($quoteRequest->requester->last_name ?? ''))
                ?: $quoteRequest->requester->email;

            return [
                'email' => $quoteRequest->requester->email,
                'name' => $name,
            ];
        }

        if ($quoteRequest->guest_email) {
            return [
                'email' => $quoteRequest->guest_email,
                'name' => $quoteRequest->guest_contact_name ?: $quoteRequest->guest_email,
            ];
        }

        return null;
    }

    private function quotesUrl(QuoteRequest $quoteRequest): string
    {
        $base = rtrim(config('app.frontend_url', config('app.url')), '/');

        if ($quoteRequest->request_by_id) {
            return $base . '/quotes';
        }

        return $base . '/register';
    }

    private function send(string $email, object $mailable): void
    {
        try {
            Mail::to($email)->send($mailable);
        } catch (Exception $e) {
            report($e);
        }
    }
}
