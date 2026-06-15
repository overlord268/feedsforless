<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Api\V1\Admin\Concerns\ValidatesAdminPasswordConfirmation;
use Illuminate\Foundation\Http\FormRequest;

class DeleteFflSkuMapRequest extends FormRequest
{
    use ValidatesAdminPasswordConfirmation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $this->withPasswordConfirmation($validator);
    }
}
