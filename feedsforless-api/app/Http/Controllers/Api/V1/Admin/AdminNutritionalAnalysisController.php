<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\NutritionalAnalysis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminNutritionalAnalysisController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->nutritionalAnalysis()->with(['parameter', 'measureUnit'])->get()
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'parameter_id' => ['required', 'exists:parameters,id'],
            'value' => ['required', 'numeric'],
            'measure_unit_id' => ['required', 'exists:measure_units,id'],
        ]);

        $analysis = $product->nutritionalAnalysis()->create($data);

        return response()->json([
            'data' => $analysis->load(['parameter', 'measureUnit'])
        ], 201);
    }

    public function destroy(Product $product, NutritionalAnalysis $analysis): JsonResponse
    {
        if ($analysis->product_id !== $product->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $analysis->delete();

        return response()->json([], 200);
    }
}