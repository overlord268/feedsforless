<?php

namespace App\Http\Requests\Api\V1\Conversations;

use Illuminate\Foundation\Http\FormRequest;

class SendConversationMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
            'access_token' => ['nullable', 'string', 'max:64'],
        ];
    }
}
