<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'grade' => $this->grade,
            'description' => $this->description,
            'stock_status' => $this->stock_status,
            'availability' => $this->availability,
            'status' => $this->status,
            'categories' => $this->whenLoaded('categories', fn() => $this->categories->map(fn($c) => [
                'id' => $c->id,
                'label' => $c->label,
                'slug' => $c->slug,
            ])),
            'packaging_options' => $this->whenLoaded('packaging', fn() => $this->packaging->map(function ($pack) {
                return [
                    'id' => $pack->id,
                    'packaging_type_id' => $pack->packaging_type_id,
                    'type_name' => $pack->packagingType?->name,
                    'quantity_per_pallet' => $pack->quantity_per_pallet,
                    'base_price_per_unit' => $pack->base_price_per_unit,
                ];
            })),
        ];
    }
}