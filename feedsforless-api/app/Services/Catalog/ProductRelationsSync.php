<?php

namespace App\Services\Catalog;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductPackaging;

class ProductRelationsSync
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public static function sync(Product $product, array $validated): void
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
                $tiers = self::normalizeVolumeTiers($pack['volume_tiers'] ?? []);
                $mode = self::resolvePackagingPricingMode($tiers);
                foreach ($tiers as $tier) {
                    $pp->tiers()->create([
                        'tier_name' => $tier['tier_name'],
                        'min_quantity' => $tier['min_quantity'] ?? 1,
                        'max_quantity' => $tier['max_quantity'] ?? null,
                        'pricing_mode' => $mode,
                        'discount_percentage' => $mode === 'percentage' ? ($tier['discount_percentage'] ?? 0) : 0,
                        'fixed_price' => $mode === 'fixed_price' ? ($tier['fixed_price'] ?? null) : null,
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
                    'measure_unit_id' => ! empty($row['measure_unit_id']) ? $row['measure_unit_id'] : null,
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

    /**
     * @param  array<int, array<string, mixed>>  $tiers
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeVolumeTiers(array $tiers): array
    {
        if ($tiers === []) {
            return [];
        }

        $tiers[0]['min_quantity'] = 1;
        $count = count($tiers);
        for ($i = 1; $i < $count; $i++) {
            $prevMax = $tiers[$i - 1]['max_quantity'] ?? null;
            if ($prevMax !== null && $prevMax !== '') {
                $tiers[$i]['min_quantity'] = (int) $prevMax;
            }
        }

        if ($count > 0) {
            $tiers[$count - 1]['max_quantity'] = null;
        }

        return $tiers;
    }

    /**
     * @param  array<int, array<string, mixed>>  $tiers
     */
    private static function resolvePackagingPricingMode(array $tiers): string
    {
        if ($tiers === []) {
            return 'percentage';
        }

        return ($tiers[0]['pricing_mode'] ?? 'percentage') === 'fixed_price' ? 'fixed_price' : 'percentage';
    }
}
