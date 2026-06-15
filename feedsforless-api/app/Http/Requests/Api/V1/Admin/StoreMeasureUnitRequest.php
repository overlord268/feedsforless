<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\Admin\Concerns\HasCatalogSlugRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreMeasureUnitRequest extends FormRequest
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
            'notation' => ['nullable', 'string', 'max:20'],
        ], $this->catalogSlugRules('measure_units'));
    }
}
