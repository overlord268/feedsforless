<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'dry_run' => ['sometimes', 'boolean'],
            'decisions' => ['sometimes', 'nullable', 'array'],
            'decisions.*' => ['string', 'in:apply,skip'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('decisions') && is_string($this->input('decisions'))) {
            $decoded = json_decode($this->input('decisions'), true);
            if (is_array($decoded)) {
                $this->merge(['decisions' => $decoded]);
            }
        }
    }
}
