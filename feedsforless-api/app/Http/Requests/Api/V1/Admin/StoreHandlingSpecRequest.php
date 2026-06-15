<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\Admin\Concerns\HasCatalogSlugRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreHandlingSpecRequest extends FormRequest
{
    use HasCatalogSlugRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'label' => ['required', 'string', 'max:255'],
            'requirement' => ['nullable', 'string'],
        ], $this->catalogSlugRules('handling_specs'));
    }
}
