<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVolumePricingTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tier_name' => ['sometimes', 'required', 'string', 'max:255'],
            'min_quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'max_quantity' => ['nullable', 'integer', 'gt:min_quantity'],
            'discount_percentage' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}