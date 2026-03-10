<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\B2B\Models\Address;
use App\Domains\Quotes\Models\QuoteRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Quotes\QuoteRequestResource;
use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    /**
     * Stats for the logged-in client dashboard.
     */
    public function stats(Request $request)
    {
        $userId = $request->user()->id;

        $quotes = QuoteRequest::where('request_by_id', $userId);
        $totalQuotes = (clone $quotes)->count();
        $pendingQuotes = (clone $quotes)->where('status', 'pending')->count();
        $quotedQuotes = (clone $quotes)->where('status', 'quoted')->count();
        $acceptedQuotes = (clone $quotes)->where('status', 'accepted')->count();

        $addressesCount = Address::where('user_id', $userId)->count();

        $recentQuotes = QuoteRequest::with(['items.product', 'items.packagingType'])
            ->where('request_by_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'total_quotes' => $totalQuotes,
                'pending_quotes' => $pendingQuotes,
                'quoted_quotes' => $quotedQuotes,
                'accepted_quotes' => $acceptedQuotes,
                'addresses_count' => $addressesCount,
                'recent_quotes' => QuoteRequestResource::collection($recentQuotes)->resolve(),
            ],
        ]);
    }
}
