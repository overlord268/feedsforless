<?php

namespace App\Services;

use App\Domains\B2B\Models\User;
use App\Domains\Conversations\Models\Conversation;
use App\Domains\Conversations\Models\ConversationMessage;
use App\Domains\Quotes\Models\QuoteRequest;
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
            ->general()
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
            if ($conversation && $conversation->isGeneralConversation() && $this->guestTokenMatches($conversation, $accessToken)) {
                if ($conversation->status === 'closed') {
                    $conversation->update(['status' => 'open']);
                }

                return ['conversation' => $conversation, 'access_token' => null];
            }
        }

        $existing = Conversation::query()
            ->general()
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

    public function findOrCreateForQuote(QuoteRequest $quote): Conversation
    {
        $existing = Conversation::query()->forQuote($quote->id)->first();

        if ($existing) {
            return $existing;
        }

        $data = [
            'quote_request_id' => $quote->id,
            'status' => 'open',
        ];

        if ($quote->request_by_id) {
            $user = $quote->requester;
            $data['user_id'] = $user->id;
            $data['guest_email'] = $user->email;
            $data['guest_name'] = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: null;
        } else {
            $data['guest_email'] = $quote->guest_email ? strtolower(trim($quote->guest_email)) : null;
            $data['guest_name'] = $this->quoteGuestDisplayName($quote);
            $data['guest_access_token'] = Str::random(48);
        }

        return Conversation::create($data);
    }

    public function authorizeQuoteAccess(QuoteRequest $quote, ?User $user, bool $isAdmin = false): bool
    {
        if ($isAdmin) {
            return true;
        }

        if ($user && $quote->request_by_id === $user->id) {
            return true;
        }

        return false;
    }

    public function authorizeQuoteConversationAccess(
        Conversation $conversation,
        QuoteRequest $quote,
        ?User $user,
        ?string $guestToken,
        bool $isAdmin = false
    ): bool {
        if ($conversation->quote_request_id !== $quote->id) {
            return false;
        }

        if ($isAdmin) {
            return true;
        }

        if ($user && $conversation->user_id === $user->id) {
            return true;
        }

        if ($guestToken && $this->guestTokenMatches($conversation, $guestToken)) {
            return true;
        }

        return false;
    }

    public function ensureQuoteReferenceInGeneralChat(QuoteRequest $quote): void
    {
        $general = $this->resolveGeneralConversationForQuote($quote);

        if (!$general) {
            return;
        }

        $alreadyPosted = $general->messages()
            ->where('message_type', ConversationMessage::TYPE_QUOTE_REFERENCE)
            ->where('metadata->quote_request_id', $quote->id)
            ->exists();

        if ($alreadyPosted) {
            return;
        }

        $label = "RFQ #{$quote->id}";

        $general->messages()->create([
            'sender_type' => ConversationMessage::SENDER_SYSTEM,
            'message_type' => ConversationMessage::TYPE_QUOTE_REFERENCE,
            'body' => "Quote conversation for {$label}. Open the quote page to continue this discussion.",
            'metadata' => [
                'quote_request_id' => $quote->id,
                'label' => $label,
                'admin_path' => "/admin/quotes/{$quote->id}",
                'customer_path' => "/quotes/{$quote->id}",
            ],
            'read_by_admin_at' => now(),
            'read_by_customer_at' => null,
        ]);

        $general->update(['last_message_at' => now()]);
    }

    public function sendQuoteChatMessage(
        QuoteRequest $quote,
        Conversation $conversation,
        string $senderType,
        string $body,
        ?User $senderUser = null
    ): ConversationMessage {
        $message = $this->sendMessage($conversation, $senderType, $body, $senderUser);
        $this->ensureQuoteReferenceInGeneralChat($quote);

        return $message;
    }

    private function resolveGeneralConversationForQuote(QuoteRequest $quote): ?Conversation
    {
        if ($quote->request_by_id) {
            return $this->findOrCreateForUser($quote->requester);
        }

        if (!$quote->guest_email) {
            return null;
        }

        $name = $this->quoteGuestDisplayName($quote);

        return $this->findOrCreateForGuest(
            email: $quote->guest_email,
            name: $name,
            conversationId: null,
            accessToken: null,
        )['conversation'];
    }

    private function quoteGuestDisplayName(QuoteRequest $quote): ?string
    {
        $fromParts = trim(($quote->guest_first_name ?? '') . ' ' . ($quote->guest_last_name ?? ''));

        if ($fromParts !== '') {
            return $fromParts;
        }

        return $quote->guest_contact_name ? trim($quote->guest_contact_name) : null;
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
        return $this->unreadGeneralChatCountForAdmin() + $this->unreadQuoteChatCountForAdmin();
    }

    public function unreadGeneralChatCountForAdmin(): int
    {
        return ConversationMessage::query()
            ->whereHas('conversation', fn ($q) => $q->general())
            ->whereIn('sender_type', [
                ConversationMessage::SENDER_GUEST,
                ConversationMessage::SENDER_CUSTOMER,
            ])
            ->whereNull('read_by_admin_at')
            ->count();
    }

    public function unreadQuoteChatCountForAdmin(): int
    {
        return ConversationMessage::query()
            ->whereHas('conversation', fn ($q) => $q->whereNotNull('quote_request_id'))
            ->whereIn('sender_type', [
                ConversationMessage::SENDER_GUEST,
                ConversationMessage::SENDER_CUSTOMER,
            ])
            ->whereNull('read_by_admin_at')
            ->count();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{quote_request_id: int, quote_chat_unread_count: int, customer_name: string, latest_message: array|null}>
     */
    public function quoteChatNotificationsForAdmin(int $limit = 8): \Illuminate\Support\Collection
    {
        return QuoteRequest::query()
            ->with([
                'requester',
                'quoteConversation.latestMessage',
            ])
            ->withCount('unreadQuoteChatMessages as quote_chat_unread_count')
            ->whereHas('unreadQuoteChatMessages')
            ->orderByDesc(
                Conversation::query()
                    ->select('last_message_at')
                    ->whereColumn('conversations.quote_request_id', 'quote_requests.id')
                    ->limit(1)
            )
            ->limit($limit)
            ->get()
            ->map(fn (QuoteRequest $quote) => [
                'quote_request_id' => $quote->id,
                'quote_chat_unread_count' => (int) $quote->quote_chat_unread_count,
                'customer_name' => $this->quoteCustomerName($quote),
                'latest_message' => $quote->quoteConversation?->latestMessage
                    ? [
                        'id' => $quote->quoteConversation->latestMessage->id,
                        'body' => $quote->quoteConversation->latestMessage->body,
                        'created_at' => $quote->quoteConversation->latestMessage->created_at?->toIso8601String(),
                    ]
                    : null,
            ]);
    }

    private function quoteCustomerName(QuoteRequest $quote): string
    {
        if ($quote->requester) {
            $name = trim(($quote->requester->first_name ?? '') . ' ' . ($quote->requester->last_name ?? ''));

            return $name !== '' ? $name : (string) $quote->requester->email;
        }

        return (string) ($quote->guest_contact_name ?: $quote->guest_email ?: 'Guest');
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
