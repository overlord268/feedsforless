<?php

namespace App\Services\Catalog;

use App\Domains\Catalog\Models\Category;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FflSkuCodeDeriver
{
    /**
     * Derive 3-letter CAT code from the category slug/label (no manual map).
     */
    public function catFromCategory(Category $category): string
    {
        $slug = Str::slug($category->label);
        if ($category->slug !== '') {
            $slug = $category->slug;
        }

        $slug = strtolower($slug);

        if (str_contains($slug, 'urea')) {
            return 'URE';
        }

        if (str_contains($slug, 'na-buffer') || $slug === 'na-buffers') {
            return 'NAB';
        }

        $parts = explode('-', $slug);

        if (count($parts) >= 2) {
            return strtoupper(substr(preg_replace('/[^a-z]/', '', $parts[0]), 0, 3));
        }

        $compact = strtoupper(preg_replace('/[^a-z]/', '', $slug));

        return substr($compact, 0, 3) ?: 'UNK';
    }

    /**
     * Derive PROD code from product name (no manual map).
     */
    public function prodFromProductName(string $productName): string
    {
        $name = trim($productName);
        if ($name === '') {
            throw new InvalidArgumentException('Product name is required for FFL SKU generation.');
        }

        $base = trim((string) preg_split('/\s[\(\d%]/', $name, 2)[0]);
        $words = array_values(array_filter(
            preg_split('/\s+/', $base),
            fn (string $w) => ! in_array(strtolower($w), ['feed', 'grade'], true)
        ));

        if ($words === []) {
            throw new InvalidArgumentException('Product name is required for FFL SKU generation.');
        }

        $first = preg_replace('/[^a-zA-Z0-9]/', '', $words[0]);
        $lowerName = strtolower($name);

        if (str_contains($lowerName, 'sodium') && str_contains($lowerName, 'bicarbonate')) {
            return 'NAHCO3';
        }

        $lowerFirst = strtolower($first);
        if (str_starts_with($lowerFirst, 'monodicalcium')) {
            return 'MDCAL';
        }
        if (str_starts_with($lowerFirst, 'monocalcium')) {
            return 'MOCAL';
        }
        if (str_starts_with($lowerFirst, 'dicalcium')) {
            return 'DICAL';
        }

        if (count($words) === 1) {
            return strtoupper(substr($first, 0, min(5, max(4, strlen($first)))));
        }

        if (count($words) === 2) {
            $second = preg_replace('/[^a-zA-Z0-9]/', '', $words[1]);

            if (strtolower($words[0]) === 'magnesium' && strtolower($words[1]) === 'oxide') {
                return 'MGOX';
            }

            if (strtolower($words[0]) === 'buffer') {
                return 'BPAC';
            }

            return strtoupper(substr($first, 0, 2).substr($second, 0, 2));
        }

        return strtoupper(substr($first, 0, 5));
    }
}
