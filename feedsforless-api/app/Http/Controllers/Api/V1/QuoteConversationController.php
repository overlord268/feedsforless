<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Conversations\Models\ConversationMessage;
use App\Domains\Quotes\Models\QuoteRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Conversations\SendConversationMessageRequest;
use App\Http\Resources\Api\V1\Conversations\ConversationMessageResource;
use App\Http\Resources\Api\V1\Conversations\ConversationResource;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteConversationController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversations
    ) {}

    public function show(Request $request, QuoteRequest $quoteRequest): JsonResponse
    {
        if (!$this->conversations->authorizeQuoteAccess($quoteRequest, $request->user())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $conversation = $this->conversations->findOrCreateForQuote($quoteRequest);
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
            'quoteRequest',
        ]);

        return response()->json([
            'data' => new ConversationResource($conversation),
        ]);
    }

    public function sendMessage(
        SendConversationMessageRequest $request,
        QuoteRequest $quoteRequest
    ): JsonResponse {
        if (!$this->conversations->authorizeQuoteAccess($quoteRequest, $request->user())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $conversation = $this->conversations->findOrCreateForQuote($quoteRequest);

        if (!$this->conversations->authorizeQuoteConversationAccess(
            $conversation,
            $quoteRequest,
            $request->user(),
            null
        )) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = $request->user();

        $message = $this->conversations->sendQuoteChatMessage(
            $quoteRequest,
            $conversation,
            ConversationMessage::SENDER_CUSTOMER,
            $request->validated('body'),
            $user
        );

        return response()->json([
            'message' => 'Message sent',
            'data' => new ConversationMessageResource($message),
        ], 201);
    }
}
