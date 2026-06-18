<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Conversations\Models\Conversation;
use App\Domains\Conversations\Models\ConversationMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Conversations\SendConversationMessageRequest;
use App\Http\Resources\Api\V1\Conversations\ConversationMessageResource;
use App\Http\Resources\Api\V1\Conversations\ConversationResource;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminConversationController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversations
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = Conversation::query()
            ->general()
            ->with(['latestMessage', 'user'])
            ->withCount([
                'messages as unread_count' => function ($q) {
                    $q->whereIn('sender_type', [
                        ConversationMessage::SENDER_GUEST,
                        ConversationMessage::SENDER_CUSTOMER,
                    ])->whereNull('read_by_admin_at');
                },
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return ConversationResource::collection($conversations);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread_count' => $this->conversations->unreadCountForAdmin(),
            'general_unread_count' => $this->conversations->unreadGeneralChatCountForAdmin(),
            'quote_chat_unread_count' => $this->conversations->unreadQuoteChatCountForAdmin(),
        ]);
    }

    public function show(Conversation $conversation): JsonResponse
    {
        if ($conversation->isQuoteConversation()) {
            return response()->json(['message' => 'Open the quote page to view this conversation.'], 403);
        }

        $this->conversations->markReadByAdmin($conversation);

        $conversation->load([
            'messages' => fn ($q) => $q->orderBy('created_at'),
            'messages.sender',
            'user',
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
        if ($conversation->isQuoteConversation()) {
            return response()->json(['message' => 'Open the quote page to reply to this conversation.'], 403);
        }

        if (
            $this->conversations->isUnregisteredGuest($conversation)
            && !$conversation->guest_email
        ) {
            return response()->json([
                'message' => 'This guest conversation has no email on file. Cannot send an email reply.',
            ], 422);
        }

        $message = $this->conversations->sendMessage(
            $conversation,
            ConversationMessage::SENDER_ADMIN,
            $request->validated('body'),
            $request->user()
        );

        return response()->json([
            'message' => 'Message sent',
            'data' => new ConversationMessageResource($message),
        ], 201);
    }

    public function updateStatus(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);

        $conversation->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Conversation updated',
            'data' => new ConversationResource($conversation->fresh(['latestMessage', 'user'])),
        ]);
    }

    public function destroy(Conversation $conversation): JsonResponse
    {
        $conversation->delete();

        return response()->json([
            'message' => 'Conversation deleted successfully',
        ]);
    }
}
