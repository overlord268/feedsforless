<?php

namespace App\Domains\Catalog\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** @mixin Model */
trait HasCatalogSlug
{
    public static function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug(trim($base));
        $candidate = $slug;
        $suffix = 0;

        while (
            static::query()
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $suffix++;
            $candidate = $slug.'-'.$suffix;
        }

        return $candidate;
    }

    public static function findByImportSlug(
        string $slug,
        ?string $matchValue = null,
        string $matchColumn = 'label'
    ): ?static {
        $slug = trim($slug);

        if ($slug !== '') {
            $found = static::query()->where('slug', $slug)->first();
            if ($found) {
                return $found;
            }

            $normalized = Str::slug($slug);
            if ($normalized !== $slug) {
                $found = static::query()->where('slug', $normalized)->first();
                if ($found) {
                    return $found;
                }
            }
        }

        if ($matchValue !== null && $matchValue !== '') {
            return static::query()->where($matchColumn, $matchValue)->first();
        }

        return null;
    }
}
