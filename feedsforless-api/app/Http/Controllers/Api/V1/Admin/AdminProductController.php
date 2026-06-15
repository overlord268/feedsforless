<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\BulkProductActionRequest;
use App\Http\Requests\Api\V1\Admin\StoreProductRequest;
use App\Http\Requests\Api\V1\Admin\UpdateProductRequest;
use App\Http\Requests\Api\V1\Admin\SyncProductCategoriesRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Services\Catalog\FflSkuGenerator;
use App\Services\Catalog\ProductPublishValidator;
use App\Services\Catalog\ProductRelationsSync;
use App\Services\Catalog\ProductSlugGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class AdminProductController extends Controller
{
    private const NESTED_KEYS = [
        'category_ids', 'handling_spec_ids', 'application_ids', 'related_product_ids',
        'packaging', 'nutritional_analysis', 'specifications',
    ];

    public function __construct(
        private readonly FflSkuGenerator $fflSkuGenerator,
        private readonly ProductSlugGenerator $slugGenerator,
        private readonly ProductPublishValidator $publishValidator
    ) {}

    public function index(\Illuminate\Http\Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(500, (int) $request->input('per_page', 15)));
        $products = Product::withTrashed()
            ->with('packaging')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = DB::transaction(function () use ($request) {
                $validated = $request->validated();
                $productData = $this->productAttributesFromRequest($validated);
                if (empty($productData['stock_status'])) {
                    $productData['stock_status'] = 'in_stock';
                }
                if (empty($productData['slug'])) {
                    $productData['slug'] = $this->slugGenerator->uniqueFromName($productData['name'] ?? 'product');
                }

                $productData['sku'] = $this->fflSkuGenerator->assignUniqueSkuFromCategories(
                    $validated['category_ids'],
                    $productData['name'],
                    $productData['grade'] ?? null
                );
                $product = Product::create($productData);
                $this->syncNestedRelations($product, $validated);

                if (($productData['status'] ?? 'draft') === 'published') {
                    $product->load(['categories', 'packaging']);
                    $this->publishValidator->assertPublishable($product);
                }

                return $product->load([
                    'categories', 'handlingSpecs', 'typicalApplications', 'packaging.tiers',
                    'nutritionalAnalysis', 'specifications', 'relatedProducts',
                ]);
            });
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['sku' => [$e->getMessage()]],
            ], 422);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }
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
        try {
            $product = DB::transaction(function () use ($request, $product) {
                $validated = $request->validated();
                $wasPublished = $product->status === 'published';
                $targetStatus = $validated['status'] ?? $product->status;
                $isPublishing = ! $wasPublished && $targetStatus === 'published';

                $productData = $this->productAttributesFromRequest($validated);
                if (! empty($productData)) {
                    if (array_key_exists('name', $productData) && empty($productData['slug'])) {
                        $productData['slug'] = $this->slugGenerator->uniqueFromName(
                            $productData['name'],
                            $product->id
                        );
                    }
                    $product->update($productData);
                }
                $this->syncNestedRelations($product, $validated);

                $product->load([
                    'categories', 'handlingSpecs', 'typicalApplications', 'packaging.tiers',
                    'nutritionalAnalysis', 'specifications', 'relatedProducts',
                ]);

                if ($isPublishing) {
                    $this->publishProduct($product);
                }

                return $product->fresh([
                    'categories', 'handlingSpecs', 'typicalApplications', 'packaging.tiers',
                    'nutritionalAnalysis', 'specifications', 'relatedProducts',
                ]);
            });
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['sku' => [$e->getMessage()]],
            ], 422);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }
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
            'message' => 'Product deleted successfully',
        ], 200);
    }

    public function restore(int $product): JsonResponse
    {
        $model = Product::onlyTrashed()->findOrFail($product);
        $model->restore();
        AdminCatalogsController::forgetCache();

        return response()->json([
            'message' => 'Product restored successfully',
            'data' => new ProductResource($model->load('packaging')),
        ], 200);
    }

    public function forceDestroy(int $product): JsonResponse
    {
        $model = Product::withTrashed()->findOrFail($product);
        $model->forceDelete();
        AdminCatalogsController::forgetCache();

        return response()->json([
            'message' => 'Product permanently deleted.',
        ], 200);
    }

    public function bulkAction(BulkProductActionRequest $request): JsonResponse
    {
        $action = $request->validated('action');
        $ids = $request->validated('product_ids');

        $succeeded = 0;
        /** @var list<array{id: int, name: string|null, message: string}> $failed */
        $failed = [];

        foreach ($ids as $id) {
            $product = Product::withTrashed()->find($id);
            if ($product === null) {
                $failed[] = ['id' => $id, 'name' => null, 'message' => 'Product not found.'];

                continue;
            }

            try {
                DB::transaction(function () use ($action, $product): void {
                    match ($action) {
                        'delete' => $this->softDeleteProduct($product),
                        'force_delete' => $product->forceDelete(),
                        'restore' => $this->restoreProductModel($product),
                        'draft' => $this->setProductDraft($product),
                        'publish' => $this->publishProduct($product),
                    };
                });
                $succeeded++;
            } catch (ValidationException|InvalidArgumentException $e) {
                $message = $e instanceof ValidationException
                    ? collect($e->errors())->flatten()->first()
                    : $e->getMessage();
                $failed[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'message' => $message ?: 'Action failed.',
                ];
            }
        }

        AdminCatalogsController::forgetCache();

        return response()->json([
            'message' => $failed === []
                ? 'Bulk action completed successfully.'
                : "Bulk action completed with {$succeeded} succeeded and ".count($failed).' failed.',
            'data' => [
                'succeeded' => $succeeded,
                'failed' => $failed,
            ],
        ]);
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
        $attrs = collect($validated)->except([...self::NESTED_KEYS, 'sku'])->filter(fn ($v) => $v !== null)->all();
        if (! array_key_exists('description', $attrs)) {
            $attrs['description'] = '';
        }

        return $attrs;
    }

    private function syncNestedRelations(Product $product, array $validated): void
    {
        ProductRelationsSync::sync($product, $validated);
    }

    /**
     * @throws ValidationException
     * @throws InvalidArgumentException
     */
    private function publishProduct(Product $product): void
    {
        if ($product->trashed()) {
            throw ValidationException::withMessages([
                'status' => ['Restore the product before publishing.'],
            ]);
        }

        if (empty($product->slug) && ! empty($product->name)) {
            $product->update([
                'slug' => $this->slugGenerator->uniqueFromName($product->name, $product->id),
            ]);
            $product->refresh();
        }

        $product->load(['categories', 'packaging']);
        $this->publishValidator->assertPublishable($product);

        if ($product->sku === null || trim((string) $product->sku) === '') {
            $categoryIds = $product->categories()->pluck('categories.id')->all();
            $product->update([
                'sku' => $this->fflSkuGenerator->assignUniqueSkuFromCategories(
                    $categoryIds,
                    $product->name,
                    $product->grade
                ),
            ]);
        }

        if ($product->status !== 'published') {
            $product->update(['status' => 'published']);
        }
    }

    /**
     * @throws ValidationException
     */
    private function softDeleteProduct(Product $product): void
    {
        if ($product->trashed()) {
            return;
        }

        $product->delete();
    }

    /**
     * @throws ValidationException
     */
    private function restoreProductModel(Product $product): void
    {
        if (! $product->trashed()) {
            return;
        }

        $product->restore();
    }

    /**
     * @throws ValidationException
     */
    private function setProductDraft(Product $product): void
    {
        if ($product->trashed()) {
            throw ValidationException::withMessages([
                'status' => ['Restore the product before changing status.'],
            ]);
        }

        if ($product->status !== 'draft') {
            $product->update(['status' => 'draft']);
        }
    }
}
