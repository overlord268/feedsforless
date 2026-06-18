<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Quotes\Models\QuoteRequest;
use App\Domains\Quotes\Models\QuoteRequestItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateQuotePricesRequest;
use App\Http\Resources\Api\V1\Quotes\QuoteRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use App\Services\ConversationService;
use App\Services\QuoteRequestNotifier;

class AdminQuoteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $quotes = QuoteRequest::query()
            ->with(['requester', 'items.product', 'items.packagingType'])
            ->withCount('unreadQuoteChatMessages as quote_chat_unread_count')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return QuoteRequestResource::collection($quotes);
    }

    /**
     * Notifications for admin header: pending count and recent quotes.
     */
    public function notifications(Request $request): JsonResponse
    {
        $pendingCount = QuoteRequest::where('status', 'pending')->count();
        $recent = QuoteRequest::with(['requester', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'pending_count' => $pendingCount,
            'recent' => QuoteRequestResource::collection($recent)->resolve(),
        ]);
    }

    public function chatNotifications(ConversationService $conversations): JsonResponse
    {
        $quotes = $conversations->quoteChatNotificationsForAdmin();

        return response()->json([
            'unread_count' => $conversations->unreadQuoteChatCountForAdmin(),
            'quotes' => $quotes->values()->all(),
        ]);
    }

    public function show(QuoteRequest $quoteRequest): QuoteRequestResource
    {
        $quoteRequest->load(['items.product', 'items.packagingType', 'requester.company']);
        
        return new QuoteRequestResource($quoteRequest);
    }

    public function updatePrices(UpdateQuotePricesRequest $request, QuoteRequest $quoteRequest): JsonResponse
    {
        $updatedQuote = DB::transaction(function () use ($request, $quoteRequest) {
            $totalCost = 0;

            foreach ($request->validated('items') as $itemData) {
                $item = QuoteRequestItem::where('id', $itemData['id'])
                    ->where('quote_request_id', $quoteRequest->id)
                    ->firstOrFail();

                $lineTotal = ($itemData['estimated_freight_cost'] + $itemData['estimated_product_cost']) * $item->qty;
                $totalCost += $lineTotal;

                $item->update([
                    'estimated_freight_cost' => $itemData['estimated_freight_cost'],
                    'estimated_product_cost' => $itemData['estimated_product_cost'],
                    'line_total_cost' => $lineTotal,
                ]);
            }

            $quoteRequest->update([
                'total_estimated_cost' => $totalCost,
                'status' => 'quoted',
            ]);

            return $quoteRequest->load(['items.product', 'items.packagingType', 'requester']);
        });

        app(QuoteRequestNotifier::class)->sendQuoted($updatedQuote);

        return response()->json([
            'message' => 'Quote prices updated successfully',
            'data' => new QuoteRequestResource($updatedQuote)
        ]);
    }

    public function updateStatus(Request $request, QuoteRequest $quoteRequest): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,quoted,accepted,rejected,expired,cancelled',
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'customer_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $previousStatus = $quoteRequest->status;

        $quoteRequest->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? $quoteRequest->admin_note,
            'customer_message' => array_key_exists('customer_message', $validated)
                ? $validated['customer_message']
                : $quoteRequest->customer_message,
        ]);

        $quoteRequest = $quoteRequest->fresh(['requester', 'items.product', 'items.packagingType']);
        $notifier = app(QuoteRequestNotifier::class);

        if (
            $previousStatus !== $validated['status']
            && in_array($validated['status'], ['rejected', 'cancelled', 'expired'], true)
        ) {
            $notifier->sendRejected($quoteRequest, $quoteRequest->customer_message);
        }

        return response()->json([
            'message' => 'Quote status updated',
            'data' => new QuoteRequestResource($quoteRequest)
        ]);
    }
}