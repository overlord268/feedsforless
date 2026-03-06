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
            'admin_note' => $this->admin_note,
            'delivery_zip' => $this->delivery_zip,
            'requires_liftgate' => (bool) $this->requires_liftgate,
            'requires_appointment' => (bool) $this->requires_appointment,
            'total_estimated_cost' => $this->total_estimated_cost,
            'customer_name' => $this->whenLoaded('requester', fn () => trim($this->requester->first_name . ' ' . $this->requester->last_name) ?: $this->requester->email),
            'requester' => $this->whenLoaded('requester', fn () => [
                'id' => $this->requester->id,
                'email' => $this->requester->email,
                'first_name' => $this->requester->first_name,
                'last_name' => $this->requester->last_name,
            ]),
            'items' => QuoteRequestItemResource::collection($this->whenLoaded('items')),
        ];
    }
}