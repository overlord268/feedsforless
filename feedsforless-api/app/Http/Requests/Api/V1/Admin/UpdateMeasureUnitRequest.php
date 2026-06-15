<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Domains\Catalog\Models\MeasureUnit;
use App\Http\Requests\Api\V1\Admin\Concerns\HasCatalogSlugRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMeasureUnitRequest extends FormRequest
{
    use HasCatalogSlugRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var MeasureUnit|null $measureUnit */
        $measureUnit = $this->route('measure_unit');

        return array_merge([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'notation' => ['nullable', 'string', 'max:20'],
        ], $this->catalogSlugRules('measure_units', $measureUnit?->id));
    }
}
