<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductPackaging;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreProductRequest;
use App\Http\Requests\Api\V1\Admin\UpdateProductRequest;
use App\Http\Requests\Api\V1\Admin\SyncProductCategoriesRequest;
use App\Http\Resources\Api\V1\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class AdminProductController extends Controller
{
    private const NESTED_KEYS = [
        'category_ids', 'handling_spec_ids', 'application_ids', 'related_product_ids',
        'packaging', 'nutritional_analysis', 'specifications',
    ];

    public function index(\Illuminate\Http\Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(500, (int) $request->input('per_page', 15)));
        $products = Product::orderBy('created_at', 'desc')->paginate($perPage);
        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = DB::transaction(function () use ($request) {
            $productData = $this->productAttributesFromRequest($request->validated());
            if (empty($productData['stock_status'])) {
                $productData['stock_status'] = 'in_stock';
            }
            $product = Product::create($productData);
            $this->syncNestedRelations($product, $request->validated());
            return $product->load([
                'categories', 'handlingSpecs', 'typicalApplications', 'packaging.tiers',
                'nutritionalAnalysis', 'specifications', 'relatedProducts',
            ]);
        });
        AdminCatalogsController::forgetCache();
        return response()->json([
            'message' => 'Product created successfully',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show(Product $product): ProductResource
    {
        $product->load([
            'categories', 'handlingSpecs', 'typicalApplications', 'packaging.tiers',
            'nutritionalAnalysis', 'specifications', 'relatedProducts',
        ]);
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = DB::transaction(function () use ($request, $product) {
            $productData = $this->productAttributesFromRequest($request->validated());
            if (!empty($productData)) {
                $product->update($productData);
            }
            $this->syncNestedRelations($product, $request->validated());
            return $product->load([
                'categories', 'handlingSpecs', 'typicalApplications', 'packaging.tiers',
                'nutritionalAnalysis', 'specifications', 'relatedProducts',
            ]);
        });
        AdminCatalogsController::forgetCache();
        return response()->json([
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product),
        ], 200);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        AdminCatalogsController::forgetCache();

        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    }

    public function syncCategories(SyncProductCategoriesRequest $request, Product $product): JsonResponse
    {
        $product->categories()->sync($request->validated('category_ids'));
        return response()->json([
            'message' => 'Categories synced successfully',
            'data' => new ProductResource($product->load('categories')),
        ], 200);
    }

    private function productAttributesFromRequest(array $validated): array
    {
        $attrs = collect($validated)->except(self::NESTED_KEYS)->filter(fn ($v) => $v !== null)->all();
        if (!array_key_exists('description', $attrs)) {
            $attrs['description'] = '';
        }
        return $attrs;
    }

    private function syncNestedRelations(Product $product, array $validated): void
    {
        if (array_key_exists('category_ids', $validated)) {
            $product->categories()->sync($validated['category_ids'] ?? []);
        }
        if (array_key_exists('handling_spec_ids', $validated)) {
            $product->handlingSpecs()->sync($validated['handling_spec_ids'] ?? []);
        }
        if (array_key_exists('application_ids', $validated)) {
            $product->typicalApplications()->sync($validated['application_ids'] ?? []);
        }
        if (array_key_exists('related_product_ids', $validated)) {
            $product->relatedProducts()->delete();
            foreach (array_unique($validated['related_product_ids'] ?? []) as $linkId) {
                if ((int) $linkId === (int) $product->id) {
                    continue;
                }
                $product->relatedProducts()->create(['link_id' => $linkId, 'label' => '']);
            }
        }
        if (array_key_exists('packaging', $validated) && is_array($validated['packaging'])) {
            $product->load('packaging');
            $product->packaging->each(fn (ProductPackaging $p) => $p->tiers()->delete());
            $product->packaging()->delete();
            foreach ($validated['packaging'] as $pack) {
                $pp = $product->packaging()->create([
                    'packaging_type_id' => $pack['packaging_type_id'],
                    'quantity_per_pallet' => $pack['quantity_per_pallet'],
                    'base_price_per_unit' => $pack['base_price_per_unit'],
                ]);
                foreach ($pack['volume_tiers'] ?? [] as $tier) {
                    $pp->tiers()->create([
                        'tier_name' => $tier['tier_name'],
                        'min_quantity' => $tier['min_quantity'] ?? 0,
                        'max_quantity' => $tier['max_quantity'] ?? null,
                        'discount_percentage' => $tier['discount_percentage'] ?? 0,
                    ]);
                }
            }
        }
        if (array_key_exists('nutritional_analysis', $validated) && is_array($validated['nutritional_analysis'])) {
            $product->nutritionalAnalysis()->delete();
            foreach ($validated['nutritional_analysis'] as $row) {
                $value = $row['value'] ?? '';
                $product->nutritionalAnalysis()->create([
                    'nutritional_parameter_id' => $row['nutritional_parameter_id'],
                    'value' => $value === '' || $value === null ? 0 : (float) $value,
                    'measure_unit_id' => !empty($row['measure_unit_id']) ? $row['measure_unit_id'] : null,
                ]);
            }
        }
        if (array_key_exists('specifications', $validated) && is_array($validated['specifications'])) {
            $product->specifications()->delete();
            foreach ($validated['specifications'] as $row) {
                $product->specifications()->create([
                    'parameter_id' => $row['parameter_id'],
                    'test_method_id' => $row['test_method_id'],
                    'specification' => $row['specification'],
                    'measure_unit_id' => $row['measure_unit_id'],
                ]);
            }
        }
    }
}