<?php

namespace App\Services;

use App\Domains\B2B\Models\User;
use App\Domains\Conversations\Models\Conversation;
use App\Domains\Conversations\Models\ConversationMessage;
use App\Mail\ChatMessageNotificationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Exception;

class ConversationService
{
    public function findOrCreateForUser(User $user): Conversation
    {
        $existing = Conversation::query()
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->orderByDesc('last_message_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        return Conversation::create([
            'user_id' => $user->id,
            'guest_email' => $user->email,
            'guest_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: null,
            'status' => 'open',
        ]);
    }

    /**
     * @return array{conversation: Conversation, access_token: string|null}
     */
    public function findOrCreateForGuest(string $email, ?string $name, ?int $conversationId, ?string $accessToken): array
    {
        $email = strtolower(trim($email));

        if ($conversationId && $accessToken) {
            $conversation = Conversation::find($conversationId);
            if ($conversation && $this->guestTokenMatches($conversation, $accessToken)) {
                if ($conversation->status === 'closed') {
                    $conversation->update(['status' => 'open']);
                }

                return ['conversation' => $conversation, 'access_token' => null];
            }
        }

        $existing = Conversation::query()
            ->whereNull('user_id')
            ->where('guest_email', $email)
            ->where('status', 'open')
            ->orderByDesc('last_message_at')
            ->first();

        if ($existing && $existing->guest_access_token) {
            return [
                'conversation' => $existing,
                'access_token' => $existing->guest_access_token,
            ];
        }

        $token = Str::random(48);

        $conversation = Conversation::create([
            'guest_email' => $email,
            'guest_name' => $name ? trim($name) : null,
            'guest_access_token' => $token,
            'status' => 'open',
        ]);

        return ['conversation' => $conversation, 'access_token' => $token];
    }

    public function guestTokenMatches(Conversation $conversation, string $token): bool
    {
        return $conversation->guest_access_token
            && hash_equals($conversation->guest_access_token, $token);
    }

    public function authorizeCustomerAccess(Conversation $conversation, ?User $user, ?string $guestToken): bool
    {
        if ($user && $conversation->user_id === $user->id) {
            return true;
        }

        if ($guestToken && $this->guestTokenMatches($conversation, $guestToken)) {
            return true;
        }

        return false;
    }

    public function attachGuestConversationToUser(Conversation $conversation, User $user): void
    {
        if ($conversation->user_id) {
            return;
        }

        $conversation->update([
            'user_id' => $user->id,
            'guest_email' => $user->email,
        ]);
    }

    public function sendMessage(
        Conversation $conversation,
        string $senderType,
        string $body,
        ?User $senderUser = null
    ): ConversationMessage {
        return DB::transaction(function () use ($conversation, $senderType, $body, $senderUser) {
            $message = $conversation->messages()->create([
                'sender_type' => $senderType,
                'sender_user_id' => $senderUser?->id,
                'body' => trim($body),
                'read_by_admin_at' => $senderType === ConversationMessage::SENDER_ADMIN ? now() : null,
                'read_by_customer_at' => $senderType !== ConversationMessage::SENDER_ADMIN ? now() : null,
            ]);

            $conversation->update([
                'last_message_at' => $message->created_at,
                'status' => 'open',
            ]);

            $this->notifyByEmail($conversation, $message);

            return $message->load('sender');
        });
    }

    public function markReadByAdmin(Conversation $conversation): void
    {
        $conversation->messages()
            ->whereIn('sender_type', [
                ConversationMessage::SENDER_GUEST,
                ConversationMessage::SENDER_CUSTOMER,
            ])
            ->whereNull('read_by_admin_at')
            ->update(['read_by_admin_at' => now()]);
    }

    public function markReadByCustomer(Conversation $conversation): void
    {
        $conversation->messages()
            ->where('sender_type', ConversationMessage::SENDER_ADMIN)
            ->whereNull('read_by_customer_at')
            ->update(['read_by_customer_at' => now()]);
    }

    public function unreadCountForAdmin(): int
    {
        return ConversationMessage::query()
            ->whereIn('sender_type', [
                ConversationMessage::SENDER_GUEST,
                ConversationMessage::SENDER_CUSTOMER,
            ])
            ->whereNull('read_by_admin_at')
            ->count();
    }

    /**
     * Visitor without a B2B account (chat started as guest).
     */
    public function isUnregisteredGuest(Conversation $conversation): bool
    {
        return $conversation->user_id === null
            && is_string($conversation->guest_email)
            && $conversation->guest_email !== '';
    }

    public function guestResumeChatUrl(Conversation $conversation): string
    {
        $base = rtrim(config('app.frontend_url', config('app.url')), '/');
        $query = http_build_query([
            'openChat' => '1',
            'conversation' => $conversation->id,
            'chat_token' => $conversation->guest_access_token,
        ]);

        return "{$base}/?{$query}";
    }

    public function ensureGuestCanReceiveEmail(Conversation $conversation): bool
    {
        return $this->isUnregisteredGuest($conversation);
    }

    private function notifyByEmail(Conversation $conversation, ConversationMessage $message): void
    {
        try {
            $conversation->refresh();

            if ($message->isFromStaff()) {
                $this->notifyCustomerOfStaffReply($conversation, $message);
            }

            // Customer → admin: in-app chat only (no sales inbox email).
        } catch (Exception $e) {
            report($e);
        }
    }

    private function notifyCustomerOfStaffReply(Conversation $conversation, ConversationMessage $message): void
    {
        // Only unregistered guests receive email; logged-in customers use in-app chat.
        if (!$this->isUnregisteredGuest($conversation)) {
            return;
        }

        $email = $conversation->guest_email;
        if (!$email) {
            report(new Exception(
                "Staff reply on conversation #{$conversation->id} but no guest email to notify."
            ));

            return;
        }

        Mail::to($email)->send(new ChatMessageNotificationMail(
            conversation: $conversation,
            chatMessage: $message,
            recipientName: $conversation->customerDisplayName(),
            isToCustomer: true,
            isGuestWithoutAccount: true,
            actionUrl: $this->guestResumeChatUrl($conversation),
        ));
    }
}
