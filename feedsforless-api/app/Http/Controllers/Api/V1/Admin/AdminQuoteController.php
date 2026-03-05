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

class AdminQuoteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $quotes = QuoteRequest::with(['items.product', 'items.packagingType'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return QuoteRequestResource::collection($quotes);
    }

    public function show(QuoteRequest $quoteRequest): QuoteRequestResource
    {
        $quoteRequest->load(['items.product', 'items.packagingType']);
        
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

            return $quoteRequest->load(['items.product', 'items.packagingType']);
        });
        return response()->json([
            'message' => 'Quote prices updated successfully',
            'data' => new QuoteRequestResource($updatedQuote)
        ]);
    }

    public function updateStatus(Request $request, QuoteRequest $quote): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,quoted,accepted,rejected,expired,cancelled'
        ]);

        $quote->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Quote status updated successfully',
            'data' => new QuoteRequestResource($quote->fresh(['items.product', 'items.packagingType']))
        ]);
    }
}