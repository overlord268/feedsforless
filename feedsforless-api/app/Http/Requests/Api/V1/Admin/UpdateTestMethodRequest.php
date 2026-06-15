<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Domains\Catalog\Models\TestMethod;
use App\Http\Requests\Api\V1\Admin\Concerns\HasCatalogSlugRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTestMethodRequest extends FormRequest
{
    use HasCatalogSlugRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TestMethod|null $testMethod */
        $testMethod = $this->route('test_method');

        return array_merge([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
        ], $this->catalogSlugRules('test_methods', $testMethod?->id));
    }
}
