<?php

namespace App\Services\Quotes;

use App\Data\QuoteLeadRow;
use App\Domains\B2B\Models\User;
use App\Domains\Quotes\Models\QuoteRequest;
use App\Support\UsZipStateResolver;
use Illuminate\Support\Collection;

class QuoteLeadService
{
    public const FILTER_UNREGISTERED_WITH_QUOTES = 'unregistered_with_quotes';

    public const FILTER_WITHOUT_ACCEPTED_QUOTE = 'without_accepted_quote';

    public const FILTER_REGISTERED_NO_QUOTES = 'registered_no_quotes';

    /**
     * @return array<string, array{number: int, label: string, description: string}>
     */
    public static function filterDefinitions(): array
    {
        return [
            self::FILTER_UNREGISTERED_WITH_QUOTES => [
                'number' => 1,
                'label' => 'Unregistered users with at least one quote',
                'description' => 'Unregistered users who have submitted at least one quote request.',
            ],
            self::FILTER_WITHOUT_ACCEPTED_QUOTE => [
                'number' => 2,
                'label' => 'Users without an accepted quote',
                'description' => 'Registered or guest users who have quotes but none with accepted status.',
            ],
            self::FILTER_REGISTERED_NO_QUOTES => [
                'number' => 3,
                'label' => 'Registered users without quotes',
                'description' => 'Registered customer accounts that have not created any quote request.',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function filterLabels(): array
    {
        return collect(self::filterDefinitions())
            ->mapWithKeys(fn (array $def, string $key) => [$key => $def['label']])
            ->all();
    }

    /**
     * @return array{number: int, label: string, description: string}
     */
    public function definitionFor(string $filter): array
    {
        return self::filterDefinitions()[$filter] ?? [
            'number' => 0,
            'label' => $filter,
            'description' => $filter,
        ];
    }

    public function labelFor(string $filter): string
    {
        return $this->definitionFor($filter)['label'];
    }

    /**
     * @return Collection<int, QuoteLeadRow>
     */
    public function leadsFor(string $filter): Collection
    {
        return match ($filter) {
            self::FILTER_UNREGISTERED_WITH_QUOTES => $this->unregisteredWithQuotes(),
            self::FILTER_WITHOUT_ACCEPTED_QUOTE => $this->withoutAcceptedQuote(),
            self::FILTER_REGISTERED_NO_QUOTES => $this->registeredWithoutQuotes(),
            default => collect(),
        };
    }

    /**
     * @return Collection<int, QuoteLeadRow>
     */
    private function unregisteredWithQuotes(): Collection
    {
        $quotes = QuoteRequest::query()
            ->whereNull('request_by_id')
            ->whereNotNull('guest_email')
            ->orderByDesc('created_at')
            ->get()
            ->unique(fn (QuoteRequest $quote) => strtolower($quote->guest_email));

        return $quotes
            ->map(fn (QuoteRequest $quote) => $this->fromGuestQuote($quote))
            ->values();
    }

    /**
     * @return Collection<int, QuoteLeadRow>
     */
    private function withoutAcceptedQuote(): Collection
    {
        $leads = collect();

        $guestEmailsWithAccepted = QuoteRequest::query()
            ->whereNull('request_by_id')
            ->where('status', 'accepted')
            ->whereNotNull('guest_email')
            ->pluck('guest_email')
            ->map(fn ($email) => strtolower($email))
            ->unique();

        $guestQuotes = QuoteRequest::query()
            ->whereNull('request_by_id')
            ->whereNotNull('guest_email')
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (QuoteRequest $quote) => ! $guestEmailsWithAccepted->contains(strtolower($quote->guest_email)))
            ->unique(fn (QuoteRequest $quote) => strtolower($quote->guest_email));

        foreach ($guestQuotes as $quote) {
            $leads->push($this->fromGuestQuote($quote));
        }

        $users = User::role('customer')
            ->with('company')
            ->whereHas('quoteRequests')
            ->whereDoesntHave('quoteRequests', fn ($query) => $query->where('status', 'accepted'))
            ->get();

        foreach ($users as $user) {
            $latestQuote = $user->quoteRequests()->latest()->first();
            $leads->push($this->fromRegisteredUser($user, $latestQuote));
        }

        return $leads
            ->unique(fn (QuoteLeadRow $row) => strtolower($row->email))
            ->values();
    }

    /**
     * @return Collection<int, QuoteLeadRow>
     */
    private function registeredWithoutQuotes(): Collection
    {
        return User::role('customer')
            ->with('company')
            ->whereDoesntHave('quoteRequests')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (User $user) => $this->fromRegisteredUser($user))
            ->values();
    }

    private function fromGuestQuote(QuoteRequest $quote): QuoteLeadRow
    {
        $quotesCount = QuoteRequest::query()
            ->whereNull('request_by_id')
            ->where('guest_email', $quote->guest_email)
            ->count();

        return new QuoteLeadRow(
            legalBusinessName: (string) ($quote->guest_company_name ?: '—'),
            firstName: (string) ($quote->guest_first_name ?: $this->splitContactName($quote->guest_contact_name)[0]),
            lastName: (string) ($quote->guest_last_name ?: $this->splitContactName($quote->guest_contact_name)[1]),
            email: (string) $quote->guest_email,
            businessEmail: (string) $quote->guest_email,
            phone: (string) ($quote->guest_phone ?: '—'),
            zipCode: (string) ($quote->delivery_zip ?: '—'),
            state: UsZipStateResolver::resolve($quote->delivery_zip),
            quotesCount: $quotesCount,
            isRegistered: false,
        );
    }

    private function fromRegisteredUser(User $user, ?QuoteRequest $latestQuote = null): QuoteLeadRow
    {
        $zip = $latestQuote?->delivery_zip
            ?? $user->quoteRequests()->latest()->value('delivery_zip')
            ?? '—';

        return new QuoteLeadRow(
            legalBusinessName: (string) ($user->company?->name ?: '—'),
            firstName: (string) ($user->first_name ?: '—'),
            lastName: (string) ($user->last_name ?: '—'),
            email: (string) $user->email,
            businessEmail: (string) $user->email,
            phone: (string) ($user->phone ?: '—'),
            zipCode: (string) $zip,
            state: UsZipStateResolver::resolve($zip !== '—' ? $zip : null),
            quotesCount: $latestQuote
                ? $user->quoteRequests()->count()
                : 0,
            isRegistered: true,
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitContactName(?string $contactName): array
    {
        if (! $contactName) {
            return ['—', '—'];
        }

        $parts = preg_split('/\s+/', trim($contactName), 2);

        return [$parts[0] ?? '—', $parts[1] ?? '—'];
    }
}
