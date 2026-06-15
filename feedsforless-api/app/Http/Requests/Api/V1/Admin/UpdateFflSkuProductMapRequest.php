<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\Admin\Concerns\ValidatesAdminPasswordConfirmation;
use App\Models\FflSkuProductMap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFflSkuProductMapRequest extends FormRequest
{
    use ValidatesAdminPasswordConfirmation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var FflSkuProductMap $map */
        $map = $this->route('productMap');

        return [
            'product_name' => ['required', 'string', 'max:255', Rule::unique('ffl_sku_product_maps', 'product_name')->ignore($map->id)],
            'code' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/', Rule::unique('ffl_sku_product_maps', 'code')->ignore($map->id)],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $this->withPasswordConfirmation($validator);
    }
}
