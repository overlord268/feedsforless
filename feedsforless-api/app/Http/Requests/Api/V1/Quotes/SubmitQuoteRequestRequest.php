<?php

namespace App\Http\Requests\Api\V1\Quotes;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuoteRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rfq_list_id' => ['required', 'integer', 'exists:rfq_lists,id'],
            'delivery_zip' => ['required', 'string', 'max:20'],
            'requires_liftgate' => ['nullable', 'boolean'],
            'requires_appointment' => ['nullable', 'boolean'],
            'target_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
        ];
    }
}