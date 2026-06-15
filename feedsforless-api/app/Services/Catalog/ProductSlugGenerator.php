<?php

namespace App\Services\Catalog;

use App\Domains\Catalog\Models\Product;
use Illuminate\Support\Str;

class ProductSlugGenerator
{
    public function uniqueFromName(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name !== '' ? $name : 'product');
        $slug = $base;
        $n = 0;

        while ($this->slugExists($slug, $excludeId)) {
            $n++;
            $slug = $base.'-'.$n;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $excludeId): bool
    {
        $query = Product::withTrashed()->where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
