<?php

namespace App\Http\Requests\Api\V1\Quotes;

use Illuminate\Foundation\Http\FormRequest;

class AddProductToRfqListRequest extends FormRequest
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
            'session_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}