<?php

namespace App\Domains\B2B\DTOs;

readonly class RegisterB2bCustomerDTO
{
    public function __construct(
        public string $companyName,
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
        public ?string $phone = null,
        public ?string $jobTitle = null,
        public ?string $taxClassification = null,
        public ?string $taxRegistrationNumber = null,
    ) {}
}