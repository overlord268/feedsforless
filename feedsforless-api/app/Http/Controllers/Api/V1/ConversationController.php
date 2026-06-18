<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Conversations\Models\Conversation;
use App\Domains\Conversations\Models\ConversationMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Conversations\SendConversationMessageRequest;
use App\Http\Requests\Api\V1\Conversations\StartConversationRequest;
use App\Http\Resources\Api\V1\Conversations\ConversationMessageResource;
use App\Http\Resources\Api\V1\Conversations\ConversationResource;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversations
    ) {}

    public function current(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $conversation = $this->conversations->findOrCreateForUser($user);
            $this->loadCustomerUnreadCount($conversation);
            $conversation->loadMissing('latestMessage');

            return $this->conversationResponse($conversation, null);
        }

        $session = $this->guestSessionFromRequest($request);
        if (!$session) {
            return response()->json(['data' => null]);
        }

        $conversation = Conversation::find($session['id']);
        if (!$conversation || !$this->conversations->guestTokenMatches($conversation, $session['token'])) {
            return response()->json(['data' => null]);
        }

        $this->loadCustomerUnreadCount($conversation);
        $conversation->loadMissing('latestMessage');

        return $this->conversationResponse($conversation, null);
    }

    public function start(StartConversationRequest $request): JsonResponse
    {
        $user = $request->user();
        $initialMessage = $request->validated('message');

        if ($user) {
            $conversation = $this->conversations->findOrCreateForUser($user);

            if ($initialMessage) {
                $this->conversations->sendMessage(
                    $conversation,
                    ConversationMessage::SENDER_CUSTOMER,
                    $initialMessage,
                    $user
                );
            }

            return $this->conversationResponse($conversation->fresh(['latestMessage']), null, 201);
        }

        $result = $this->conversations->findOrCreateForGuest(
            email: $request->validated('email'),
            name: $request->validated('name'),
            conversationId: $request->validated('conversation_id'),
            accessToken: $request->validated('access_token'),
        );

        $conversation = $result['conversation'];
        $token = $result['access_token'] ?? $conversation->guest_access_token;

        if ($initialMessage) {
            $this->conversations->sendMessage(
                $conversation,
                ConversationMessage::SENDER_GUEST,
                $initialMessage
            );
        }

        return $this->conversationResponse(
            $conversation->fresh(['latestMessage']),
            $token,
            201
        );
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        if (!$this->authorizeCustomer($request, $conversation)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($conversation->isQuoteConversation()) {
            return response()->json(['message' => 'Use the quote page to view this conversation.'], 403);
        }

        $this->conversations->markReadByCustomer($conversation);

        $conversation->loadCount([
            'messages as unread_count' => fn ($q) => $q
                ->where('sender_type', ConversationMessage::SENDER_ADMIN)
                ->whereNull('read_by_customer_at'),
        ]);

        $conversation->load([
            'messages' => fn ($q) => $q->orderBy('created_at'),
            'messages.sender',
            'latestMessage',
        ]);

        return response()->json([
            'data' => new ConversationResource($conversation),
        ]);
    }

    public function sendMessage(
        SendConversationMessageRequest $request,
        Conversation $conversation
    ): JsonResponse {
        if (!$this->authorizeCustomer($request, $conversation)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($conversation->isQuoteConversation()) {
            return response()->json(['message' => 'Use the quote page to view this conversation.'], 403);
        }

        $user = $request->user();
        $senderType = $user
            ? ConversationMessage::SENDER_CUSTOMER
            : ConversationMessage::SENDER_GUEST;

        if ($user && !$conversation->user_id) {
            $this->conversations->attachGuestConversationToUser($conversation, $user);
        }

        $message = $this->conversations->sendMessage(
            $conversation,
            $senderType,
            $request->validated('body'),
            $user
        );

        return response()->json([
            'message' => 'Message sent',
            'data' => new ConversationMessageResource($message),
        ], 201);
    }

    private function authorizeCustomer(Request $request, Conversation $conversation): bool
    {
        return $this->conversations->authorizeCustomerAccess(
            $conversation,
            $request->user(),
            $this->guestTokenFromRequest($request)
        );
    }

    private function guestTokenFromRequest(Request $request): ?string
    {
        $token = $request->input('access_token');
        if (is_string($token) && $token !== '') {
            return $token;
        }

        $session = $this->guestSessionFromRequest($request);

        return $session['token'] ?? null;
    }

  /**
     * @return array{id: int, token: string}|null
     */
    private function guestSessionFromRequest(Request $request): ?array
    {
        $id = $request->header('X-Conversation-Id');
        $token = $request->header('X-Conversation-Token');

        if ($id && $token) {
            return ['id' => (int) $id, 'token' => $token];
        }

        return null;
    }

    private function conversationResponse(
        Conversation $conversation,
        ?string $guestToken,
        int $status = 200
    ): JsonResponse {
        $conversation->loadMissing('latestMessage');

        $payload = [
            'data' => new ConversationResource($conversation),
        ];

        if ($guestToken) {
            $payload['guest_session'] = [
                'conversation_id' => $conversation->id,
                'access_token' => $guestToken,
            ];
        }

        return response()->json($payload, $status);
    }

    private function loadCustomerUnreadCount(Conversation $conversation): void
    {
        $conversation->loadCount([
            'messages as unread_count' => fn ($q) => $q
                ->where('sender_type', ConversationMessage::SENDER_ADMIN)
                ->whereNull('read_by_customer_at'),
        ]);
    }
}
