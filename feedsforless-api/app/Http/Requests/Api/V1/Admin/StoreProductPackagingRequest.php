<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductPackagingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'packaging_type_id' => ['required', 'integer', 'exists:packaging_types,id'],
            'quantity_per_pallet' => ['required', 'integer', 'min:1'],
            'base_price_per_unit' => ['required', 'numeric', 'min:0'],
        ];
    }
}