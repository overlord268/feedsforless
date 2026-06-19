<?php

namespace App\Services\Quotes;

use App\Data\QuoteLeadRow;
use App\Domains\B2B\Models\User;
use App\Domains\Quotes\Models\QuoteRequest;
use App\Support\UsZipStateResolver;
use Illuminate\Support\Collection;

class QuoteLeadMapper
{
    /** @var array<string, int> */
    private array $guestQuoteCountsByEmail = [];

    /** @var array<int, int> */
    private array $userQuoteCountsById = [];

    /** @var array<int, string|null> */
    private array $userLatestZipById = [];

    /**
     * @param  Collection<int, QuoteRequest>|null  $guestQuotes
     * @param  Collection<int, User>|null  $users
     */
    public function preload(?Collection $guestQuotes = null, ?Collection $users = null): self
    {
        if ($guestQuotes !== null && $guestQuotes->isNotEmpty()) {
            $emails = $guestQuotes
                ->pluck('guest_email')
                ->filter()
                ->map(fn ($email) => strtolower((string) $email))
                ->unique()
                ->values();

            if ($emails->isNotEmpty()) {
                $this->guestQuoteCountsByEmail = QuoteRequest::query()
                    ->whereNull('request_by_id')
                    ->whereIn('guest_email', $emails)
                    ->selectRaw('LOWER(guest_email) as email_key, COUNT(*) as total')
                    ->groupBy('email_key')
                    ->pluck('total', 'email_key')
                    ->all();
            }
        }

        if ($users !== null && $users->isNotEmpty()) {
            $userIds = $users->pluck('id');

            $this->userQuoteCountsById = QuoteRequest::query()
                ->whereIn('request_by_id', $userIds)
                ->selectRaw('request_by_id, COUNT(*) as total')
                ->groupBy('request_by_id')
                ->pluck('total', 'request_by_id')
                ->all();

            $this->userLatestZipById = QuoteRequest::query()
                ->whereIn('request_by_id', $userIds)
                ->orderByDesc('created_at')
                ->get(['request_by_id', 'delivery_zip'])
                ->unique('request_by_id')
                ->pluck('delivery_zip', 'request_by_id')
                ->all();
        }

        return $this;
    }

    public function fromQuoteRequest(QuoteRequest $quote): QuoteLeadRow
    {
        $emailKey = strtolower((string) $quote->guest_email);
        $quotesCount = $this->guestQuoteCountsByEmail[$emailKey]
            ?? QuoteRequest::query()
                ->whereNull('request_by_id')
                ->where('guest_email', $quote->guest_email)
                ->count();

        [$firstName, $lastName] = $this->splitContactName($quote->guest_contact_name);

        return new QuoteLeadRow(
            legalBusinessName: (string) ($quote->guest_company_name ?: '—'),
            firstName: (string) ($quote->guest_first_name ?: $firstName),
            lastName: (string) ($quote->guest_last_name ?: $lastName),
            email: (string) $quote->guest_email,
            businessEmail: (string) $quote->guest_email,
            phone: (string) ($quote->guest_phone ?: '—'),
            zipCode: (string) ($quote->delivery_zip ?: '—'),
            state: UsZipStateResolver::resolve($quote->delivery_zip),
            quotesCount: (int) $quotesCount,
            isRegistered: false,
        );
    }

    public function fromUser(User $user, ?QuoteRequest $latestQuote = null): QuoteLeadRow
    {
        $zip = $latestQuote?->delivery_zip
            ?? $this->userLatestZipById[$user->id]
            ?? '—';

        $quotesCount = $latestQuote
            ? (int) ($this->userQuoteCountsById[$user->id] ?? 0)
            : 0;

        return new QuoteLeadRow(
            legalBusinessName: (string) ($user->company?->name ?: '—'),
            firstName: (string) ($user->first_name ?: '—'),
            lastName: (string) ($user->last_name ?: '—'),
            email: (string) $user->email,
            businessEmail: (string) $user->email,
            phone: (string) ($user->phone ?: '—'),
            zipCode: (string) $zip,
            state: UsZipStateResolver::resolve($zip !== '—' ? $zip : null),
            quotesCount: $quotesCount,
            isRegistered: true,
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function splitContactName(?string $contactName): array
    {
        if (! $contactName) {
            return ['—', '—'];
        }

        $parts = preg_split('/\s+/', trim($contactName), 2);

        return [$parts[0] ?? '—', $parts[1] ?? '—'];
    }
}
