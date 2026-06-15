<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\Admin\Concerns\HasCatalogSlugRules;
use App\Domains\Catalog\Models\HandlingSpec;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHandlingSpecRequest extends FormRequest
{
    use HasCatalogSlugRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var HandlingSpec|null $handlingSpec */
        $handlingSpec = $this->route('handling_spec');

        return array_merge([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'requirement' => ['nullable', 'string'],
        ], $this->catalogSlugRules('handling_specs', $handlingSpec?->id));
    }
}
