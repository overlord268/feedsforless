<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes', 
                'required', 
                'string', 
                'max:255', 
                Rule::unique('categories')->ignore($this->route('category'))
            ],
        ];
    }
}