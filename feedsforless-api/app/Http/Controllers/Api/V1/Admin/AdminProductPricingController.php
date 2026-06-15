<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\VolumePricingTier;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateGlobalProfitMarginRequest;
use App\Http\Requests\Api\V1\Admin\UpdateProductProfitMarginRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTierProfitMarginRequest;
use App\Services\Catalog\ProfitMarginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductPricingController extends Controller
{
    public function __construct(
        private readonly ProfitMarginService $profitMarginService
    ) {}

    public function showMargins(): JsonResponse
    {
        return response()->json([
            'data' => [
                'global_profit_margin_percent' => $this->profitMarginService->globalMarginPercent(),
            ],
        ]);
    }

    public function updateGlobalMargin(UpdateGlobalProfitMarginRequest $request): JsonResponse
    {
        $margin = $this->profitMarginService->setGlobalMarginPercent(
            (float) $request->validated('profit_margin_percent')
        );

        return response()->json([
            'message' => 'Global profit margin updated.',
            'data' => [
                'global_profit_margin_percent' => $margin,
            ],
        ]);
    }

    public function indexProducts(Request $request): JsonResponse
    {
        $perPage = max(1, min(500, (int) $request->input('per_page', 15)));
        $status = $request->input('status');

        $query = Product::withTrashed()
            ->with(['packaging.packagingType', 'packaging.tiers'])
            ->orderBy('name');

        if ($status === 'published') {
            $query->whereNull('deleted_at')->where('status', 'published');
        } elseif ($status === 'draft') {
            $query->whereNull('deleted_at')->where('status', '!=', 'published');
        } elseif ($status === 'deleted') {
            $query->whereNotNull('deleted_at');
        } elseif ($status === 'active') {
            $query->whereNull('deleted_at');
        }

        $paginator = $query->paginate($perPage);

        $items = $paginator->getCollection()->map(function (Product $product) {
            $preview = $this->profitMarginService->pricingPreviewForProduct($product);

            return array_merge([
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'status' => $product->status,
                'deleted_at' => $product->deleted_at,
                'profit_margin_percent' => $preview['product_profit_margin_percent'],
                'effective_margin_percent' => $preview['effective_margin_percent'],
                'margin_source' => $preview['margin_source'],
                'presentation_groups' => $preview['presentation_groups'],
                'pricing_lines' => $preview['pricing_lines'],
            ]);
        });

        $paginator->setCollection($items);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function updateProductMargin(UpdateProductProfitMarginRequest $request, Product $product): JsonResponse
    {
        $value = $request->validated('profit_margin_percent');
        $product->profit_margin_percent = $value === null ? null : (float) $value;
        $product->save();

        $product = $product->fresh(['packaging.packagingType', 'packaging.tiers']);

        return response()->json([
            'message' => $value === null ? 'Product margin reset to global.' : 'Product margin updated.',
            'data' => array_merge(['id' => $product->id], $this->profitMarginService->pricingPreviewSliceForProduct($product)),
        ]);
    }

    public function updateTierMargin(UpdateTierProfitMarginRequest $request, VolumePricingTier $tier): JsonResponse
    {
        $tier->load('productPackaging.product');
        $product = $tier->productPackaging?->product;

        if (! $product) {
            return response()->json(['message' => 'Product not found for tier.'], 404);
        }

        $value = $request->validated('profit_margin_percent');
        $tier->profit_margin_percent = $value === null ? null : (float) $value;
        $tier->save();

        $product = $product->fresh(['packaging.packagingType', 'packaging.tiers']);

        return response()->json([
            'message' => $value === null ? 'Tier margin reset to product/global default.' : 'Tier margin updated.',
            'data' => array_merge(['id' => $product->id], $this->profitMarginService->pricingPreviewSliceForProduct($product)),
        ]);
    }
}
