<?php

namespace App\Http\Requests\Api\V1\Admin\Concerns;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

trait ValidatesAdminPasswordConfirmation
{
    protected function withPasswordConfirmation(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $password = $this->input('password_confirmation');
            $user = $this->user();

            if (! is_string($password) || $password === '') {
                $validator->errors()->add('password_confirmation', 'Password confirmation is required.');

                return;
            }

            if (! $user || ! Hash::check($password, $user->password)) {
                $validator->errors()->add('password_confirmation', 'The password is incorrect.');
            }
        });
    }
}
