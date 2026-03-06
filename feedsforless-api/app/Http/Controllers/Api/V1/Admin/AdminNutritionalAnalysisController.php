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
            'data' => $product->nutritionalAnalysis()->with(['nutritionalParameter', 'measureUnit'])->get()
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'nutritional_parameter_id' => ['required', 'exists:nutritional_parameters,id'],
            'value' => ['required', 'numeric'],
            'measure_unit_id' => ['nullable', 'exists:measure_units,id'],
        ]);

        $analysis = $product->nutritionalAnalysis()->create($data);

        return response()->json([
            'data' => $analysis->load(['nutritionalParameter', 'measureUnit'])
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