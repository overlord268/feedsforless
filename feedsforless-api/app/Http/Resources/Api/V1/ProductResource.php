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
        ];
    }
}