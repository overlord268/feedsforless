<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Domains\Catalog\Models\TypicalApplication;
use App\Http\Requests\Api\V1\Admin\Concerns\HasCatalogSlugRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTypicalApplicationRequest extends FormRequest
{
    use HasCatalogSlugRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TypicalApplication|null $typicalApplication */
        $typicalApplication = $this->route('typical_application');

        return array_merge([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ], $this->catalogSlugRules('typical_applications', $typicalApplication?->id));
    }
}
