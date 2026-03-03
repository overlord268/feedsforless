<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
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
            'documents' => [
                'tds' => $this->tds_document_path,
                'sds' => $this->sds_document_path,
                'coa' => $this->coa_document_path,
            ],
            'categories' => $this->categories->pluck('label'),
            'packaging_options' => $this->packaging->map(function ($pack) {
                return [
                    'packaging_id' => $pack->id,
                    'type' => $pack->name,
                    'quantity_per_pallet' => $pack->pivot->quantity_per_pallet,
                    'base_price_per_unit' => $pack->pivot->base_price_per_unit,
                ];
            }),
        ];
    }
}