<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CustomerDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $user = request()->user();
        
        return response()->json([
            'data' => [
                'my_quotes_count' => $user->quoteRequests()->count(),
                'pending_quotes' => $user->quoteRequests()->where('status', 'pending')->count(),
                'recent_activity' => $user->quoteRequests()->orderBy('created_at', 'desc')->take(5)->get()
            ]
        ]);
    }
}
