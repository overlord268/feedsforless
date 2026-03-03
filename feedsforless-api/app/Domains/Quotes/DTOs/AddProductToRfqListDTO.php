<?php

namespace App\Domains\Quotes\DTOs;

readonly class AddProductToRfqListDTO
{
    public function __construct(
        public int $productId,
        public int $packagingTypeId,
        public int $quantity,
        public ?int $userId = null,
        public ?string $sessionId = null,
    ) {}
}