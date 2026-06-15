<?php

namespace App\Http\Resources\Api\V1\Conversations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender_type' => $this->sender_type,
            'sender_name' => $this->when(
                $this->sender_type === 'admin' && $this->relationLoaded('sender'),
                fn () => trim(($this->sender?->first_name ?? '') . ' ' . ($this->sender?->last_name ?? '')) ?: 'FeedsForLess Team'
            ),
            'body' => $this->body,
            'is_from_staff' => $this->isFromStaff(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
