<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'tax_classification' => ['nullable', 'string', 'max:255'],
            'tax_registration_number' => [
                'nullable', 
                'string', 
                'max:255', 
                Rule::unique('companies')->ignore($this->route('company'))
            ],
        ];
    }
}