<?php

namespace App\Http\Resources\Api\V1\Quotes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\ProductResource;

class QuoteRequestItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'qty' => $this->qty,
            'estimated_freight_cost' => $this->estimated_freight_cost,
            'estimated_product_cost' => $this->estimated_product_cost,
            'line_total_cost' => $this->line_total_cost,
            'product' => new ProductResource($this->whenLoaded('product')),
            'packaging_type' => $this->whenLoaded('packagingType', fn () => [
                'id' => $this->packagingType->id,
                'name' => $this->packagingType->name,
            ]),
        ];
    }
}