<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Domains\B2B\Models\User;
use App\Domains\Quotes\Models\QuoteRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Quotes\QuoteRequestResource;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $activeQuotes = QuoteRequest::where('status', 'pending')->count();
        $totalProducts = Product::count();
        $publishedProducts = Product::where('status', 'published')->count();
        $totalUsers = User::count();

        $recentQuotes = QuoteRequest::with(['requester', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $days = 7;
        $rfqByDay = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = QuoteRequest::whereDate('created_at', $date)->count();
            $rfqByDay[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'count' => $count,
            ];
        }

        return response()->json([
            'data' => [
                'active_quotes' => $activeQuotes,
                'total_products' => $totalProducts,
                'published_products' => $publishedProducts,
                'total_users' => $totalUsers,
                'recent_quotes' => QuoteRequestResource::collection($recentQuotes)->resolve(),
                'rfq_by_day' => $rfqByDay,
            ],
        ]);
    }
}