<?php

namespace App\Services\Quotes;

use App\Data\QuoteLeadRow;
use App\Domains\B2B\Models\User;
use App\Domains\Quotes\Models\QuoteRequest;
use Illuminate\Support\Collection;

class QuoteLeadService
{
    public const FILTER_UNREGISTERED_WITH_QUOTES = 'unregistered_with_quotes';

    public const FILTER_WITHOUT_ACCEPTED_QUOTE = 'without_accepted_quote';

    public const FILTER_REGISTERED_NO_QUOTES = 'registered_no_quotes';

    public function __construct(
        private readonly QuoteLeadMapper $mapper = new QuoteLeadMapper,
    ) {}

    /**
     * @return array<string, array{number: int, label: string, description: string}>
     */
    public static function filterDefinitions(): array
    {
        return [
            self::FILTER_UNREGISTERED_WITH_QUOTES => [
                'number' => 1,
                'label' => 'Guests with quotes',
                'description' => 'Unregistered users who have submitted at least one quote request.',
            ],
            self::FILTER_WITHOUT_ACCEPTED_QUOTE => [
                'number' => 2,
                'label' => 'No accepted quote',
                'description' => 'Registered or guest users who have quotes but none with accepted status.',
            ],
            self::FILTER_REGISTERED_NO_QUOTES => [
                'number' => 3,
                'label' => 'Registered, no RFQ',
                'description' => 'Registered customer accounts that have not created any quote request.',
            ],
        ];
    }

    /**
     * @return array<int, array{id: string, number: int, title: string, description: string}>
     */
    public function definitionsList(): array
    {
        return collect(self::filterDefinitions())
            ->map(fn (array $def, string $key) => [
                'id' => $key,
                'number' => $def['number'],
                'title' => $def['label'],
                'description' => $def['description'],
            ])
            ->values()
            ->all();
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
     * Lightweight segment counts for dashboard cards (no row mapping).
     *
     * @return array<string, int>
     */
    public function countsByFilter(): array
    {
        return [
            self::FILTER_UNREGISTERED_WITH_QUOTES => $this->countUnregisteredWithQuotes(),
            self::FILTER_WITHOUT_ACCEPTED_QUOTE => $this->countWithoutAcceptedQuote(),
            self::FILTER_REGISTERED_NO_QUOTES => $this->countRegisteredWithoutQuotes(),
        ];
    }

    private function countUnregisteredWithQuotes(): int
    {
        return (int) QuoteRequest::query()
            ->whereNull('request_by_id')
            ->whereNotNull('guest_email')
            ->selectRaw('COUNT(DISTINCT LOWER(guest_email)) as aggregate')
            ->value('aggregate');
    }

    private function countRegisteredWithoutQuotes(): int
    {
        return User::role('customer')
            ->whereDoesntHave('quoteRequests')
            ->count();
    }

    private function countWithoutAcceptedQuote(): int
    {
        $acceptedGuestEmails = QuoteRequest::query()
            ->whereNull('request_by_id')
            ->where('status', 'accepted')
            ->whereNotNull('guest_email')
            ->pluck('guest_email')
            ->map(fn ($email) => strtolower((string) $email))
            ->flip();

        $guestEmails = QuoteRequest::query()
            ->whereNull('request_by_id')
            ->whereNotNull('guest_email')
            ->orderByDesc('created_at')
            ->pluck('guest_email')
            ->map(fn ($email) => strtolower((string) $email))
            ->unique()
            ->reject(fn (string $email) => isset($acceptedGuestEmails[$email]));

        $userEmails = User::role('customer')
            ->whereHas('quoteRequests')
            ->whereDoesntHave('quoteRequests', fn ($query) => $query->where('status', 'accepted'))
            ->pluck('email')
            ->map(fn ($email) => strtolower((string) $email));

        return $guestEmails->merge($userEmails)->unique()->count();
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

        $this->mapper->preload(guestQuotes: $quotes);

        return $quotes
            ->map(fn (QuoteRequest $quote) => $this->mapper->fromQuoteRequest($quote))
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

        $users = User::role('customer')
            ->with('company')
            ->with(['quoteRequests' => fn ($query) => $query->latest()->limit(1)])
            ->whereHas('quoteRequests')
            ->whereDoesntHave('quoteRequests', fn ($query) => $query->where('status', 'accepted'))
            ->get();

        $this->mapper->preload(guestQuotes: $guestQuotes, users: $users);

        foreach ($guestQuotes as $quote) {
            $leads->push($this->mapper->fromQuoteRequest($quote));
        }

        foreach ($users as $user) {
            $latestQuote = $user->quoteRequests->first();
            $leads->push($this->mapper->fromUser($user, $latestQuote));
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
        $users = User::role('customer')
            ->with('company')
            ->whereDoesntHave('quoteRequests')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->mapper->preload(users: $users);

        return $users
            ->map(fn (User $user) => $this->mapper->fromUser($user))
            ->values();
    }
}
