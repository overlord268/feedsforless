<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotePricesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:quote_request_items,id'],
            'items.*.estimated_freight_cost' => ['required', 'numeric', 'min:0'],
            'items.*.estimated_product_cost' => ['required', 'numeric', 'min:0'],
        ];
    }
}