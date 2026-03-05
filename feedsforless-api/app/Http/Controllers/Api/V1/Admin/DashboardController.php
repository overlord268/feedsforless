<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        return response()->json([
            'data' => [
                'active_quotes' => 0,
                'pending_orders' => 0,
                'total_savings' => 0
            ]
        ]);
    }
}