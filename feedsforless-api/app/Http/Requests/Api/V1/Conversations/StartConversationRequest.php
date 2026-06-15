<?php

namespace App\Http\Requests\Api\V1\Conversations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                Rule::requiredIf(fn () => !$this->user() && !$this->filled('conversation_id')),
                'nullable',
                'email',
                'max:255',
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'conversation_id' => ['nullable', 'integer', 'exists:conversations,id'],
            'access_token' => ['nullable', 'string', 'max:64'],
        ];
    }
}
