<?php

namespace App\Domains\Quotes\Actions;

use App\Domains\Catalog\Models\Product;
use App\Domains\Quotes\DTOs\SubmitGuestQuoteRequestDTO;
use App\Domains\Quotes\Models\QuoteRequest;
use Illuminate\Support\Facades\DB;
use Exception;

class SubmitGuestQuoteRequestAction
{
    public function execute(SubmitGuestQuoteRequestDTO $dto): QuoteRequest
    {
        return DB::transaction(function () use ($dto) {
            $product = Product::find($dto->productId);
            if (!$product) {
                throw new Exception('Product not found.');
            }

            $quoteRequest = QuoteRequest::create([
                'request_by_id' => null,
                'guest_email' => $dto->email,
                'guest_company_name' => $dto->legalName,
                'guest_contact_name' => $dto->contactName,
                'guest_phone' => $dto->phone,
                'guest_destination_address' => $dto->destinationAddress,
                'target_address_id' => null,
                'delivery_zip' => $dto->deliveryZip,
                'requires_liftgate' => $dto->requiresLiftgate,
                'requires_appointment' => $dto->requiresAppointment,
                'status' => 'pending',
            ]);

            $quoteRequest->items()->create([
                'product_id' => $dto->productId,
                'packaging_type_id' => $dto->packagingTypeId,
                'qty' => $dto->quantity,
            ]);

            return $quoteRequest->load(['items.product', 'items.packagingType']);
        });
    }
}
