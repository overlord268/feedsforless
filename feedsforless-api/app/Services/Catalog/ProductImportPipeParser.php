<?php

namespace App\Services\Catalog;

class ProductImportPipeParser
{
    /**
     * Split a pipe-delimited string into trimmed parts (empty segments preserved).
     *
     * @return list<string>
     */
    public static function split(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        return array_map(
            static fn ($part) => trim((string) $part),
            explode('|', (string) $value)
        );
    }

    /**
     * Split into fixed-size chunks (e.g. packaging = 4 fields per item).
     *
     * @return list<list<string>>
     */
    public static function chunk(?string $value, int $fieldsPerItem): array
    {
        if ($fieldsPerItem < 1) {
            return [];
        }

        $parts = self::split($value);
        if ($parts === []) {
            return [];
        }

        $items = [];
        for ($i = 0; $i < count($parts); $i += $fieldsPerItem) {
            $chunk = array_slice($parts, $i, $fieldsPerItem);
            while (count($chunk) < $fieldsPerItem) {
                $chunk[] = '';
            }
            $items[] = $chunk;
        }

        return $items;
    }

    /**
     * Slug list columns: one slug per pipe segment (empty segments skipped).
     *
     * @return list<string>
     */
    public static function slugList(?string $value): array
    {
        return array_values(array_filter(
            self::split($value),
            static fn (string $slug) => $slug !== ''
        ));
    }
}
