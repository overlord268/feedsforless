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
            'pricing_mode' => ['sometimes', 'nullable', 'string', 'in:percentage,fixed_price'],
            'discount_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'fixed_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }
}