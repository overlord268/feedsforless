<?php

namespace App\Services\Catalog;

use App\Models\FflSkuCategoryMap;
use App\Models\FflSkuProductMap;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class FflSkuMapRepository
{
    private const CACHE_TTL = 3600;

    public function categoryMap(): array
    {
        return Cache::remember('ffl_sku.category_map', self::CACHE_TTL, function (): array {
            if ($this->hasCategoryMapsInDatabase()) {
                return FflSkuCategoryMap::query()
                    ->orderBy('label')
                    ->pluck('code', 'label')
                    ->all();
            }

            return config('ffl_sku.category_map', []);
        });
    }

    public function productMap(): array
    {
        return Cache::remember('ffl_sku.product_map', self::CACHE_TTL, function (): array {
            if ($this->hasProductMapsInDatabase()) {
                return FflSkuProductMap::query()
                    ->orderBy('product_name')
                    ->pluck('code', 'product_name')
                    ->all();
            }

            return config('ffl_sku.product_map', []);
        });
    }

    public function forgetCache(): void
    {
        Cache::forget('ffl_sku.category_map');
        Cache::forget('ffl_sku.product_map');
    }

    private function hasCategoryMapsInDatabase(): bool
    {
        return Schema::hasTable('ffl_sku_category_maps')
            && FflSkuCategoryMap::query()->exists();
    }

    private function hasProductMapsInDatabase(): bool
    {
        return Schema::hasTable('ffl_sku_product_maps')
            && FflSkuProductMap::query()->exists();
    }
}
