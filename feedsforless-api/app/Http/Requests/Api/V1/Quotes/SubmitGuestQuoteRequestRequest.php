<?php

namespace App\Http\Requests\Api\V1\Quotes;

use Illuminate\Foundation\Http\FormRequest;

class SubmitGuestQuoteRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'packaging_type_id' => ['required', 'integer', 'exists:packaging_types,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'delivery_zip' => ['required', 'string', 'max:20'],
            'destination_address' => ['nullable', 'string', 'max:500'],
            'requires_liftgate' => ['nullable', 'boolean'],
            'requires_appointment' => ['nullable', 'boolean'],
            'email' => ['required', 'email'],
            'legal_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'tax_id' => ['nullable', 'string', 'max:100'],
        ];
    }
}
