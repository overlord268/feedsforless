<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductPackaging;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreProductPackagingRequest;
use App\Http\Requests\Api\V1\Admin\UpdateProductPackagingRequest;
use Illuminate\Http\JsonResponse;

class AdminProductPackagingController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        $packaging = ProductPackaging::where('product_id', $product->id)
            ->with('packagingType')
            ->get();

        return response()->json([
            'data' => $packaging
        ], 200);
    }

    public function store(StoreProductPackagingRequest $request, Product $product): JsonResponse
    {
        $productPackaging = ProductPackaging::create([
            'product_id' => $product->id,
            'packaging_type_id' => $request->validated('packaging_type_id'),
            'quantity_per_pallet' => $request->validated('quantity_per_pallet'),
            'base_price_per_unit' => $request->validated('base_price_per_unit'),
        ]);

        return response()->json([
            'message' => 'Product packaging added successfully',
            'data' => $productPackaging->load('packagingType')
        ], 201);
    }

    public function show(Product $product, ProductPackaging $packaging): JsonResponse
    {
        if ($packaging->product_id !== $product->id) {
            return response()->json(['message' => 'Packaging does not belong to this product'], 400);
        }

        return response()->json([
            'data' => $packaging->load('packagingType')
        ], 200);
    }

    public function update(UpdateProductPackagingRequest $request, Product $product, ProductPackaging $packaging): JsonResponse
    {
        if ($packaging->product_id !== $product->id) {
            return response()->json(['message' => 'Packaging does not belong to this product'], 400);
        }

        $packaging->update($request->validated());

        return response()->json([
            'message' => 'Product packaging updated successfully',
            'data' => $packaging->load('packagingType')
        ], 200);
    }

    public function destroy(Product $product, ProductPackaging $packaging): JsonResponse
    {
        if ($packaging->product_id !== $product->id) {
            return response()->json(['message' => 'Packaging does not belong to this product'], 400);
        }

        $packaging->delete();

        return response()->json([
            'message' => 'Product packaging removed successfully'
        ], 200);
    }
}