<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductProfitMarginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profit_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ];
    }
}
