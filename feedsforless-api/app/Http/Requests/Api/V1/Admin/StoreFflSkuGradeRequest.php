<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\Admin\Concerns\ValidatesAdminPasswordConfirmation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFflSkuGradeRequest extends FormRequest
{
    use ValidatesAdminPasswordConfirmation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_spec' => ['required', 'string', 'max:500', Rule::unique('ffl_sku_grades', 'grade_spec')],
            'sku_code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9]+$/'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $this->withPasswordConfirmation($validator);
    }
}
