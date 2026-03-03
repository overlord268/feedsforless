<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tax_classification' => ['nullable', 'string', 'max:255'],
            'tax_registration_number' => ['nullable', 'string', 'max:255', 'unique:companies,tax_registration_number'],
        ];
    }
}