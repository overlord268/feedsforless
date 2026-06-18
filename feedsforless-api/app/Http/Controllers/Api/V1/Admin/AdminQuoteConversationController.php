<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Conversations\Models\ConversationMessage;
use App\Domains\Quotes\Models\QuoteRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Conversations\SendConversationMessageRequest;
use App\Http\Resources\Api\V1\Conversations\ConversationMessageResource;
use App\Http\Resources\Api\V1\Conversations\ConversationResource;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminQuoteConversationController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversations
    ) {}

    public function show(Request $request, QuoteRequest $quoteRequest): JsonResponse
    {
        $conversation = $this->conversations->findOrCreateForQuote($quoteRequest);

        if ($request->boolean('mark_read')) {
            $this->conversations->markReadByAdmin($conversation);
        }

        $conversation->load([
            'messages' => fn ($q) => $q->orderBy('created_at'),
            'messages.sender',
            'latestMessage',
            'quoteRequest',
            'user',
        ]);

        return response()->json([
            'data' => new ConversationResource($conversation),
        ]);
    }

    public function sendMessage(
        SendConversationMessageRequest $request,
        QuoteRequest $quoteRequest
    ): JsonResponse {
        $conversation = $this->conversations->findOrCreateForQuote($quoteRequest);

        if (
            $this->conversations->isUnregisteredGuest($conversation)
            && !$conversation->guest_email
        ) {
            return response()->json([
                'message' => 'This guest quote has no email on file. Cannot send an email reply.',
            ], 422);
        }

        $this->conversations->markReadByAdmin($conversation);

        $message = $this->conversations->sendQuoteChatMessage(
            $quoteRequest,
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
}
