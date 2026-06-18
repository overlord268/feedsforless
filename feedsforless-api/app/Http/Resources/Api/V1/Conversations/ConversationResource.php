<?php

namespace App\Http\Resources\Api\V1\Conversations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'quote_request_id' => $this->quote_request_id,
            'is_quote_conversation' => $this->isQuoteConversation(),
            'customer_name' => $this->customerDisplayName(),
            'customer_email' => $this->customerEmail(),
            'is_guest' => $this->user_id === null,
            'is_unregistered_guest' => $this->user_id === null && filled($this->guest_email),
            'notifies_by_email' => $this->user_id === null && filled($this->guest_email),
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'unread_count' => (int) ($this->unread_count ?? 0),
            'latest_message' => new ConversationMessageResource($this->whenLoaded('latestMessage')),
            'messages' => ConversationMessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}
