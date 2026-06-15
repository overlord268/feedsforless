<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Domains\Catalog\Models\NutritionalParameter;
use App\Http\Requests\Api\V1\Admin\Concerns\HasCatalogSlugRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNutritionalParameterRequest extends FormRequest
{
    use HasCatalogSlugRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var NutritionalParameter|null $nutritionalParameter */
        $nutritionalParameter = $this->route('nutritional_parameter');

        return array_merge([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'notation' => ['nullable', 'string', 'max:50'],
        ], $this->catalogSlugRules('nutritional_parameters', $nutritionalParameter?->id));
    }
}
