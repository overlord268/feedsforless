<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Domains\Catalog\Models\Parameter;
use App\Http\Requests\Api\V1\Admin\Concerns\HasCatalogSlugRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateParameterRequest extends FormRequest
{
    use HasCatalogSlugRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Parameter|null $parameter */
        $parameter = $this->route('parameter');

        return array_merge([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
        ], $this->catalogSlugRules('parameters', $parameter?->id));
    }
}
