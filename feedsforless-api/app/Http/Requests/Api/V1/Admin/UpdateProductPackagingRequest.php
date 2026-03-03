<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductPackagingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity_per_pallet' => ['sometimes', 'required', 'integer', 'min:1'],
            'base_price_per_unit' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}