<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Catalog\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categoriesTree = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('label', 'asc')
            ->get();

        return response()->json([
            'data' => $categoriesTree
        ], 200);
    }
}