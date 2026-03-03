<?php

namespace App\Http\Resources\Api\V1\Quotes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\ProductResource;

class RfqListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'product' => new ProductResource($this->whenLoaded('product')),
            'packaging_type' => $this->whenLoaded('packagingType', function () {
                return [
                    'id' => $this->packagingType->id,
                    'name' => $this->packagingType->name,
                ];
            }),
        ];
    }
}