<?php

namespace App\Http\Resources\Api\V1\Quotes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'delivery_zip' => $this->delivery_zip,
            'requires_liftgate' => (bool) $this->requires_liftgate,
            'requires_appointment' => (bool) $this->requires_appointment,
            'total_estimated_cost' => $this->total_estimated_cost,
            'items' => QuoteRequestItemResource::collection($this->whenLoaded('items')),
        ];
    }
}