<?php

namespace App\Domains\Quotes\Actions;

use App\Domains\Quotes\DTOs\SubmitQuoteRequestDTO;
use App\Domains\Quotes\Models\QuoteRequest;
use App\Domains\Quotes\Models\RfqList;
use Illuminate\Support\Facades\DB;
use Exception;

class SubmitQuoteRequestAction
{
    public function execute(SubmitQuoteRequestDTO $dto): QuoteRequest
    {
        return DB::transaction(function () use ($dto) {
            $rfqList = RfqList::with('items')
                ->where('id', $dto->rfqListId)
                ->where('user_id', $dto->userId)
                ->where('status', 'active')
                ->first();

            if (!$rfqList || $rfqList->items->isEmpty()) {
                throw new Exception("Invalid or empty RFQ List.");
            }

            $quoteRequest = QuoteRequest::create([
                'request_by_id' => $dto->userId,
                'target_address_id' => $dto->targetAddressId,
                'delivery_zip' => $dto->deliveryZip,
                'requires_liftgate' => $dto->requiresLiftgate,
                'requires_appointment' => $dto->requiresAppointment,
                'status' => 'pending',
            ]);

            foreach ($rfqList->items as $item) {
                $quoteRequest->items()->create([
                    'product_id' => $item->product_id,
                    'packaging_type_id' => $item->packaging_type_id,
                    'qty' => $item->quantity,
                ]);
            }

            $rfqList->update(['status' => 'submitted']);

            return $quoteRequest->load(['items.product', 'items.packagingType']);
        });
    }
}