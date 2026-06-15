<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\Admin\Concerns\ValidatesAdminPasswordConfirmation;
use App\Models\FflSkuCategoryMap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFflSkuCategoryMapRequest extends FormRequest
{
    use ValidatesAdminPasswordConfirmation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var FflSkuCategoryMap $map */
        $map = $this->route('categoryMap');

        return [
            'label' => ['required', 'string', 'max:255', Rule::unique('ffl_sku_category_maps', 'label')->ignore($map->id)],
            'code' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $this->withPasswordConfirmation($validator);
    }
}
