<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Domains\Quotes\Models\QuoteRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => [
                'total_products' => Product::count(),
                'active_quotes' => QuoteRequest::whereIn('status', ['pending', 'quoted'])->count(),
                'total_users' => User::count(),
                'recent_quotes' => QuoteRequest::with('user')->orderBy('created_at', 'desc')->take(5)->get()
            ]
        ]);
    }
}
