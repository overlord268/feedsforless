<?php

namespace App\Services\Catalog;

use App\Domains\Catalog\Models\Product;
use App\Http\Controllers\Api\V1\Admin\AdminCatalogsController;
use App\Http\Resources\Api\V1\ProductResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AiProductService
{
    public function __construct(
        private readonly ProductSlugPayloadBuilder $payloadBuilder,
        private readonly FflSkuGenerator $fflSkuGenerator
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     * @return array{product: Product, created: bool, resource: ProductResource}
     */
    public function upsert(array $validated): array
    {
        $registry = new MasterSlugRegistry;
        $registry->bootstrapFromDatabase();

        $canonical = $this->payloadBuilder->normalizeFromAiPayload($validated);
        $refErrors = $this->payloadBuilder->validateReferences($canonical, $registry);

        if ($refErrors !== []) {
            throw ValidationException::withMessages([
                'references' => $refErrors,
            ]);
        }

        $relations = $this->payloadBuilder->buildRelationsPayload($canonical, $registry);
        if ($relations === null) {
            throw ValidationException::withMessages([
                'references' => ['Could not resolve one or more slug references to catalog IDs.'],
            ]);
        }

        $slug = $validated['slug'];
        $attributes = $this->productAttributesFromValidated($validated);
        $existing = Product::where('slug', $slug)->first();
        $created = $existing === null;

        try {
            $product = DB::transaction(function () use ($existing, $attributes, $relations) {
                if ($existing) {
                    $existing->update($attributes);
                    ProductRelationsSync::sync($existing, $relations);

                    return $existing;
                }

                $attributes['sku'] = $this->fflSkuGenerator->assignUniqueSkuFromCategories(
                    $relations['category_ids'],
                    $attributes['name'],
                    $attributes['grade']
                );
                $product = Product::create($attributes);
                ProductRelationsSync::sync($product, $relations);

                return $product;
            });
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'sku' => [$e->getMessage()],
            ]);
        }

        AdminCatalogsController::forgetCache();

        $product->load([
            'categories', 'handlingSpecs', 'typicalApplications', 'packaging.tiers',
            'nutritionalAnalysis', 'specifications', 'relatedProducts',
        ]);

        return [
            'product' => $product,
            'created' => $created,
            'resource' => new ProductResource($product),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function productAttributesFromValidated(array $validated): array
    {
        $stockStatus = $validated['stock_status'] ?? 'in_stock';

        return [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'grade' => $validated['grade'] ?? null,
            'base_price_ref' => isset($validated['base_price_ref']) ? (float) $validated['base_price_ref'] : null,
            'description' => $validated['description'] ?? '',
            'status' => $validated['status'],
            'stock_status' => $stockStatus,
            'availability' => $validated['availability'] ?? null,
            'lead_time' => $this->leadTimeDate($validated['lead_time_days'] ?? null),
            'max_lead_time' => $this->leadTimeDate($validated['max_lead_time_days'] ?? null),
            'origin_address' => $validated['origin_address'] ?? null,
            'shelf_life_template' => $validated['shelf_life_template'] ?? null,
            'market_trends_link' => $validated['market_trends_link'] ?? null,
            'tds_document_path' => $validated['tds_document_url'] ?? null,
            'sds_document_path' => $validated['sds_document_url'] ?? null,
            'coa_document_path' => $validated['coa_document_url'] ?? null,
        ];
    }

    private function leadTimeDate(mixed $days): ?string
    {
        if ($days === null || $days === '') {
            return null;
        }

        $value = (int) $days;

        return $value > 0 ? now()->addDays($value)->toDateString() : null;
    }
}
