<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['sometimes', 'required', 'string', Rule::unique('products')->ignore($this->route('product'))],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stock_status' => ['sometimes', 'required', 'string', 'in:in_stock,out_of_stock,backorder'],
            'availability' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', 'in:draft,published,archived'],
        ];
    }
}