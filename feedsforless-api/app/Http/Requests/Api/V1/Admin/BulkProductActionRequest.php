<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkProductActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', Rule::in(['delete', 'force_delete', 'restore', 'publish', 'draft'])],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'min:1'],
        ];
    }
}
