<?php

namespace App\Services\Catalog;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\VolumePricingTier;
use App\Models\CatalogSetting;
use Illuminate\Support\Collection;

class ProfitMarginService
{
    public const GLOBAL_MARGIN_KEY = 'default_profit_margin_percent';

    public function globalMarginPercent(): float
    {
        $stored = CatalogSetting::query()
            ->where('key', self::GLOBAL_MARGIN_KEY)
            ->value('value');

        if ($stored !== null && $stored !== '' && is_numeric($stored)) {
            return max(0, (float) $stored);
        }

        return max(0, (float) config('catalog.default_profit_margin_percent', 15));
    }

    public function setGlobalMarginPercent(float $marginPercent): float
    {
        $marginPercent = max(0, min(999, $marginPercent));

        CatalogSetting::query()->updateOrCreate(
            ['key' => self::GLOBAL_MARGIN_KEY],
            ['value' => (string) $marginPercent]
        );

        return $marginPercent;
    }

    public function effectiveMarginPercent(Product $product): float
    {
        if ($product->profit_margin_percent !== null && $product->profit_margin_percent !== '') {
            return max(0, (float) $product->profit_margin_percent);
        }

        return $this->globalMarginPercent();
    }

    public function marginSource(Product $product): string
    {
        return ($product->profit_margin_percent !== null && $product->profit_margin_percent !== '')
            ? 'product'
            : 'global';
    }

    public function effectiveMarginPercentForTier(Product $product, ?VolumePricingTier $tier): float
    {
        if ($tier !== null && $tier->profit_margin_percent !== null && $tier->profit_margin_percent !== '') {
            return max(0, (float) $tier->profit_margin_percent);
        }

        return $this->effectiveMarginPercent($product);
    }

    public function marginSourceForTier(Product $product, ?VolumePricingTier $tier): string
    {
        if ($tier !== null && $tier->profit_margin_percent !== null && $tier->profit_margin_percent !== '') {
            return 'tier';
        }

        return $this->marginSource($product);
    }

    /**
     * @param  array<string, mixed>  $tier
     */
    public function tierPayloadFromModel(VolumePricingTier $tier): array
    {
        return [
            'id' => $tier->id,
            'tier_name' => $tier->tier_name,
            'min_quantity' => $tier->min_quantity,
            'max_quantity' => $tier->max_quantity,
            'pricing_mode' => $tier->pricing_mode ?? 'percentage',
            'discount_percentage' => $tier->discount_percentage,
            'fixed_price' => $tier->fixed_price !== null ? (float) $tier->fixed_price : null,
            'profit_margin_percent' => $tier->profit_margin_percent !== null
                ? (float) $tier->profit_margin_percent
                : null,
        ];
    }

    public function applyMargin(float $basePrice, float $marginPercent): float
    {
        return round($basePrice * (1 + max(0, $marginPercent) / 100), 2);
    }

    /**
     * Customer-facing price for one tier (matches public catalog logic).
     */
    public function customerPriceForTier(array $tier, float $presentationBase, float $marginPercent): ?float
    {
        $mode = $tier['pricing_mode'] ?? 'percentage';

        if ($mode === 'fixed_price') {
            $fixed = $tier['fixed_price'] ?? null;
            if ($fixed === null || $fixed === '') {
                return null;
            }

            return $this->applyMargin((float) $fixed, $marginPercent);
        }

        if ($presentationBase <= 0) {
            return null;
        }

        $disc = max(0, (float) ($tier['discount_percentage'] ?? 0));
        $marginedBase = $this->applyMargin($presentationBase, $marginPercent);

        return round($marginedBase * (1 - $disc / 100), 2);
    }

    public function formatQuantityRange(?int $min, mixed $max): string
    {
        $minLabel = max(0, (int) ($min ?? 0));
        $maxNum = $max !== null && $max !== '' ? (int) $max : null;

        if ($maxNum !== null && $maxNum > 0) {
            return "{$minLabel}–{$maxNum} T";
        }

        return "{$minLabel}–∞ T";
    }

    /**
     * @return array{
     *     effective_margin_percent: float,
     *     margin_source: string,
     *     product_profit_margin_percent: float|null,
     *     presentation_groups: list<array<string, mixed>>,
     *     pricing_lines: list<array<string, mixed>>
     * }
     */
    public function pricingPreviewForProduct(Product $product): array
    {
        $marginPercent = $this->effectiveMarginPercent($product);
        $marginSource = $this->marginSource($product);

        $product->loadMissing(['packaging.packagingType', 'packaging.tiers']);

        $presentationGroups = [];
        $pricingLines = [];

        if ($product->packaging->isNotEmpty()) {
            foreach ($product->packaging as $pack) {
                $presentationBase = (float) ($pack->base_price_per_unit ?? 0);
                $label = $pack->packagingType?->name ?? 'Presentation';
                $tiers = $pack->relationLoaded('tiers')
                    ? $pack->tiers->sortBy('min_quantity')->values()
                    : collect();

                $hasTiers = $tiers->isNotEmpty();
                $tierRows = [];

                if ($hasTiers) {
                    foreach ($tiers as $tier) {
                        $tierPayload = $this->tierPayloadFromModel($tier);
                        $tierMargin = $this->effectiveMarginPercentForTier($product, $tier);
                        $tierMarginSource = $this->marginSourceForTier($product, $tier);

                        $internalBase = ($tierPayload['pricing_mode'] === 'fixed_price')
                            ? (float) ($tierPayload['fixed_price'] ?? 0)
                            : $presentationBase;

                        $customerPrice = $this->customerPriceForTier($tierPayload, $presentationBase, $tierMargin);

                        $tierRows[] = [
                            'id' => $tier->id,
                            'tier_name' => $tier->tier_name,
                            'quantity_range' => $this->formatQuantityRange($tier->min_quantity, $tier->max_quantity),
                            'pricing_mode' => $tierPayload['pricing_mode'],
                            'internal_base_price' => $internalBase > 0 ? round($internalBase, 2) : null,
                            'discount_percentage' => $tierPayload['pricing_mode'] === 'percentage'
                                ? (float) ($tier->discount_percentage ?? 0)
                                : null,
                            'tier_profit_margin_percent' => $tier->profit_margin_percent !== null
                                ? (float) $tier->profit_margin_percent
                                : null,
                            'effective_margin_percent' => $tierMargin,
                            'margin_percent' => $tierMargin,
                            'margin_source' => $tierMarginSource,
                            'price_with_margin' => $customerPrice,
                        ];

                        $pricingLines[] = [
                            'source' => 'tier',
                            'presentation_id' => $pack->id,
                            'tier_id' => $tier->id,
                            'label' => $label.' · '.$this->formatQuantityRange($tier->min_quantity, $tier->max_quantity),
                            'base_price' => $internalBase > 0 ? round($internalBase, 2) : 0,
                            'margin_percent' => $tierMargin,
                            'margin_source' => $tierMarginSource,
                            'price_with_margin' => $customerPrice,
                        ];
                    }
                } else {
                    $pricingLines[] = [
                        'source' => 'presentation',
                        'presentation_id' => $pack->id,
                        'label' => $label,
                        'base_price' => $presentationBase,
                        'margin_percent' => $marginPercent,
                        'margin_source' => $marginSource,
                        'price_with_margin' => $this->applyMargin($presentationBase, $marginPercent),
                    ];
                }

                $presentationGroups[] = [
                    'presentation_id' => $pack->id,
                    'label' => $label,
                    'has_tiers' => $hasTiers,
                    'presentation_base' => $presentationBase > 0 ? round($presentationBase, 2) : null,
                    'base_price' => $hasTiers ? null : ($presentationBase > 0 ? round($presentationBase, 2) : null),
                    'margin_percent' => $marginPercent,
                    'margin_source' => $marginSource,
                    'price_with_margin' => $hasTiers ? null : $this->applyMargin($presentationBase, $marginPercent),
                    'tiers' => $tierRows,
                ];
            }
        } elseif ($product->base_price_ref !== null && $product->base_price_ref !== '') {
            $base = (float) $product->base_price_ref;
            $line = [
                'source' => 'base_price_ref',
                'presentation_id' => null,
                'label' => 'Base price ref',
                'base_price' => $base,
                'margin_percent' => $marginPercent,
                'margin_source' => $marginSource,
                'price_with_margin' => $this->applyMargin($base, $marginPercent),
            ];
            $pricingLines[] = $line;
            $presentationGroups[] = [
                'presentation_id' => null,
                'label' => 'Base price ref',
                'has_tiers' => false,
                'base_price' => round($base, 2),
                'margin_percent' => $marginPercent,
                'margin_source' => $marginSource,
                'price_with_margin' => $this->applyMargin($base, $marginPercent),
                'tiers' => [],
            ];
        }

        return [
            'effective_margin_percent' => $marginPercent,
            'margin_source' => $marginSource,
            'product_profit_margin_percent' => $product->profit_margin_percent !== null
                ? (float) $product->profit_margin_percent
                : null,
            'presentation_groups' => $presentationGroups,
            'pricing_lines' => $pricingLines,
        ];
    }

    /**
     * Bake profit margin into public catalog prices (no margin metadata returned).
     *
     * @param  array<string, mixed>  $payload  ProductResource-style array
     * @return array<string, mixed>
     */
    public function applyMarginsToPublicPayload(array $payload, Product $product): array
    {
        $product->loadMissing(['packaging.packagingType', 'packaging.tiers']);

        if (array_key_exists('base_price_ref', $payload) && $payload['base_price_ref'] !== null) {
            $payload['base_price_ref'] = $this->applyMargin(
                (float) $payload['base_price_ref'],
                $this->effectiveMarginPercent($product)
            );
        }

        $packaging = $payload['packaging_options'] ?? null;
        if ($packaging === null) {
            return $payload;
        }

        $payload['packaging_options'] = Collection::make($packaging)
            ->map(function ($pack) use ($product) {
                $pack = $this->normalizeResourceArray($pack);
                $packModel = $product->packaging->firstWhere('id', $pack['id'] ?? null);
                $presentationBase = (float) ($packModel->base_price_per_unit ?? 0);
                $tiers = $packModel?->relationLoaded('tiers') ? $packModel->tiers : collect();
                $hasTiers = $tiers->isNotEmpty();

                if ($hasTiers) {
                    $pack['volume_tiers'] = Collection::make($pack['volume_tiers'] ?? [])
                        ->map(function ($tier) use ($product, $packModel, $presentationBase) {
                            $tier = $this->normalizeResourceArray($tier);
                            $tierModel = $packModel?->tiers->firstWhere('id', $tier['id'] ?? null);
                            $margin = $this->effectiveMarginPercentForTier($product, $tierModel);
                            $tierPayload = $tierModel
                                ? $this->tierPayloadFromModel($tierModel)
                                : $tier;

                            $customerPrice = $this->customerPriceForTier($tierPayload, $presentationBase, $margin);

                            if ($customerPrice !== null) {
                                $tier['customer_price_per_ton'] = $customerPrice;
                                if (($tier['pricing_mode'] ?? 'percentage') === 'fixed_price') {
                                    $tier['fixed_price'] = $customerPrice;
                                }
                            }

                            return $tier;
                        })
                        ->all();
                } elseif (isset($pack['base_price_per_unit']) && $pack['base_price_per_unit'] !== null && $pack['base_price_per_unit'] !== '') {
                    $pack['base_price_per_unit'] = $this->applyMargin(
                        (float) $pack['base_price_per_unit'],
                        $this->effectiveMarginPercent($product)
                    );
                }

                return $pack;
            })
            ->all();

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function pricingPreviewSliceForProduct(Product $product): array
    {
        $preview = $this->pricingPreviewForProduct($product);

        return [
            'profit_margin_percent' => $preview['product_profit_margin_percent'],
            'effective_margin_percent' => $preview['effective_margin_percent'],
            'margin_source' => $preview['margin_source'],
            'presentation_groups' => $preview['presentation_groups'],
            'pricing_lines' => $preview['pricing_lines'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeResourceArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Collection) {
            return $value->all();
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        return (array) $value;
    }
}
