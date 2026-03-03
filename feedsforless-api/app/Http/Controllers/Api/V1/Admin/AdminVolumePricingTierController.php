<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\ProductPackaging;
use App\Domains\Catalog\Models\VolumePricingTier;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreVolumePricingTierRequest;
use App\Http\Requests\Api\V1\Admin\UpdateVolumePricingTierRequest;
use Illuminate\Http\JsonResponse;

class AdminVolumePricingTierController extends Controller
{
    public function index(ProductPackaging $packaging): JsonResponse
    {
        return response()->json([
            'data' => $packaging->tiers()->orderBy('min_quantity', 'asc')->get()
        ]);
    }

    public function store(StoreVolumePricingTierRequest $request, ProductPackaging $packaging): JsonResponse
    {
        $tier = $packaging->tiers()->create($request->validated());

        return response()->json([
            'message' => 'Pricing tier created successfully',
            'data' => $tier
        ], 201);
    }

    public function update(UpdateVolumePricingTierRequest $request, ProductPackaging $packaging, VolumePricingTier $tier): JsonResponse
    {
        if ($tier->product_packaging_id !== $packaging->id) {
            return response()->json(['message' => 'Tier does not belong to this packaging'], 400);
        }

        $tier->update($request->validated());

        return response()->json([
            'message' => 'Pricing tier updated successfully',
            'data' => $tier
        ]);
    }

    public function destroy(ProductPackaging $packaging, VolumePricingTier $tier): JsonResponse
    {
        if ($tier->product_packaging_id !== $packaging->id) {
            return response()->json(['message' => 'Tier does not belong to this packaging'], 400);
        }

        $tier->delete();

        return response()->json(['message' => 'Pricing tier deleted successfully']);
    }
}