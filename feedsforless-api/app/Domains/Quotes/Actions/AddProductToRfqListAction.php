<?php

namespace App\Domains\Quotes\Actions;

use App\Domains\Quotes\DTOs\AddProductToRfqListDTO;
use App\Domains\Quotes\Models\RfqList;
use Illuminate\Support\Facades\DB;

class AddProductToRfqListAction
{
    public function execute(AddProductToRfqListDTO $dto): RfqList
    {
        return DB::transaction(function () use ($dto) {
            $query = RfqList::where('status', 'active');

            if ($dto->userId) {
                $query->where('user_id', $dto->userId);
            } else {
                $query->where('session_id', $dto->sessionId);
            }

            $rfqList = $query->first();

            if (!$rfqList) {
                $rfqList = RfqList::create([
                    'user_id' => $dto->userId,
                    'session_id' => $dto->sessionId,
                    'status' => 'active',
                ]);
            }

            $item = $rfqList->items()
                ->where('product_id', $dto->productId)
                ->where('packaging_type_id', $dto->packagingTypeId)
                ->first();

            if ($item) {
                $item->increment('quantity', $dto->quantity);
            } else {
                $rfqList->items()->create([
                    'product_id' => $dto->productId,
                    'packaging_type_id' => $dto->packagingTypeId,
                    'quantity' => $dto->quantity,
                ]);
            }

            return $rfqList->load(['items.product', 'items.packagingType']);
        });
    }
}