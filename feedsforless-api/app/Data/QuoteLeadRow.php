<?php

namespace App\Data;

readonly class QuoteLeadRow
{
    public function __construct(
        public string $legalBusinessName,
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $businessEmail,
        public string $phone,
        public string $zipCode,
        public ?string $state,
        public int $quotesCount = 0,
        public bool $isRegistered = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'legal_business_name' => $this->legalBusinessName,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'business_email' => $this->businessEmail,
            'phone' => $this->phone,
            'zip_code' => $this->zipCode,
            'state' => $this->state,
            'quotes_count' => $this->quotesCount,
            'is_registered' => $this->isRegistered,
        ];
    }
}
