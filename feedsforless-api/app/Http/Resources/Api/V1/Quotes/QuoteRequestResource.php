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
            'customer_name' => $this->request_by_id
                ? $this->whenLoaded('requester', fn () => trim($this->requester->first_name . ' ' . $this->requester->last_name) ?: $this->requester->email)
                : ($this->guest_contact_name ?: $this->guest_email),
            'guest_email' => $this->guest_email,
            'guest_company_name' => $this->guest_company_name,
            'guest_contact_name' => $this->guest_contact_name,
            'guest_phone' => $this->guest_phone,
            'guest_destination_address' => $this->guest_destination_address,
            'guest_tax_id' => $this->guest_tax_id ?? null,
            'requester' => $this->whenLoaded('requester', function () {
                if ($this->requester) {
                    return [
                        'id' => $this->requester->id,
                        'email' => $this->requester->email,
                        'first_name' => $this->requester->first_name,
                        'last_name' => $this->requester->last_name,
                        'phone' => $this->requester->phone,
                        'job_title' => $this->requester->job_title,
                        'company_name' => $this->requester->relationLoaded('company') && $this->requester->company
                            ? $this->requester->company->name : null,
                        'tax_id' => $this->requester->relationLoaded('company') && $this->requester->company
                            ? $this->requester->company->tax_registration_number : null,
                    ];
                }
                // Guest RFQ: expose guest fields in same shape so frontend can show Corporate Entity Profile
                return [
                    'id' => null,
                    'email' => $this->guest_email,
                    'first_name' => null,
                    'last_name' => null,
                    'contact_name' => $this->guest_contact_name,
                    'phone' => $this->guest_phone,
                    'job_title' => null,
                    'company_name' => $this->guest_company_name,
                    'tax_id' => $this->guest_tax_id ?? null,
                ];
            }),
            'items' => QuoteRequestItemResource::collection($this->whenLoaded('items')),
        ];
    }
}