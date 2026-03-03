<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Specification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminProductSpecificationController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->specifications()->with(['testMethod', 'parameter', 'measureUnit'])->get()
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'test_method_id' => ['required', 'exists:test_methods,id'],
            'parameter_id' => ['required', 'exists:parameters,id'],
            'specification' => ['required', 'string'],
            'measure_unit_id' => ['required', 'exists:measure_units,id'],
        ]);

        $spec = $product->specifications()->create($data);

        return response()->json([
            'message' => 'Specification added successfully',
            'data' => $spec->load(['testMethod', 'parameter', 'measureUnit'])
        ], 201);
    }

    public function destroy(Product $product, Specification $specification): JsonResponse
    {
        $specification->delete();
        return response()->json(['message' => 'Specification removed']);
    }
}