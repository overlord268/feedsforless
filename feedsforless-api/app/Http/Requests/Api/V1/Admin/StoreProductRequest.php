<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stock_status' => ['required', 'string', 'in:in_stock,out_of_stock,backorder'],
            'availability' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
        ];
    }
}