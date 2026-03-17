<?php

namespace App\Domains\Quotes\DTOs;

readonly class SubmitGuestQuoteRequestDTO
{
    public function __construct(
        public int $productId,
        public int $packagingTypeId,
        public int $quantity,
        public string $deliveryZip,
        public string $email,
        public string $legalName,
        public string $contactName,
        public string $phone,
        public ?string $destinationAddress = null,
        public bool $requiresLiftgate = false,
        public bool $requiresAppointment = false,
        public ?string $taxId = null,
    ) {}
}
