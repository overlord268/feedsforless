<?php

namespace App\Domains\Quotes\DTOs;

readonly class SubmitQuoteRequestDTO
{
    public function __construct(
        public int $rfqListId,
        public int $userId,
        public string $deliveryZip,
        public bool $requiresLiftgate = false,
        public bool $requiresAppointment = false,
        public ?int $targetAddressId = null,
    ) {}
}