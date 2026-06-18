<?php

namespace App\Http\Resources\Api\V1\Conversations;

use App\Domains\Conversations\Models\ConversationMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $quoteReference = null;

        if ($this->isQuoteReference() && is_array($this->metadata)) {
            $quoteReference = [
                'quote_request_id' => $this->metadata['quote_request_id'] ?? null,
                'label' => $this->metadata['label'] ?? null,
                'admin_path' => $this->metadata['admin_path'] ?? null,
                'customer_path' => $this->metadata['customer_path'] ?? null,
            ];
        }

        return [
            'id' => $this->id,
            'sender_type' => $this->sender_type,
            'message_type' => $this->message_type ?? ConversationMessage::TYPE_TEXT,
            'sender_name' => $this->when(
                $this->sender_type === 'admin' && $this->relationLoaded('sender'),
                fn () => trim(($this->sender?->first_name ?? '') . ' ' . ($this->sender?->last_name ?? '')) ?: 'FeedsForLess Team'
            ),
            'body' => $this->body,
            'quote_reference' => $quoteReference,
            'is_system' => $this->sender_type === ConversationMessage::SENDER_SYSTEM,
            'is_from_staff' => $this->isFromStaff(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
