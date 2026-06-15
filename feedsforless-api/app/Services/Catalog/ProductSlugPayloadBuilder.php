<?php

namespace App\Services\Catalog;

use App\Domains\Catalog\Models\Product;

class ProductSlugPayloadBuilder
{
    /**
     * Normalize an Excel import row (pipe-delimited fields) to canonical slug payload.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function normalizeFromImportRow(array $row): array
    {
        $packaging = [];
        foreach (ProductImportPipeParser::chunk($row['packaging'] ?? '', 4) as $item) {
            $typeSlug = trim((string) ($item[1] ?? ''));
            if ($typeSlug === '') {
                continue;
            }
            $packaging[] = [
                'presentation_index' => (int) ($item[0] ?? 1),
                'packaging_type_slug' => $typeSlug,
                'quantity_per_pallet' => $item[2] !== '' ? (int) $item[2] : 1,
                'base_price_per_unit' => $item[3] !== '' ? (float) $item[3] : 0,
                'volume_tiers' => [],
            ];
        }

        $tiersByPresentation = [];
        foreach (self::parseImportVolumeTierItems($row['volume_tiers'] ?? '') as $tier) {
            $presentationIndex = (int) ($tier['presentation_index'] ?? 0);
            unset($tier['presentation_index']);
            $tiersByPresentation[$presentationIndex][] = $tier;
        }

        foreach ($packaging as $index => $pkg) {
            $idx = $pkg['presentation_index'];
            $packaging[$index]['volume_tiers'] = $tiersByPresentation[$idx] ?? [];
        }

        $nutritionalAnalysis = [];
        foreach (ProductImportPipeParser::chunk($row['nutritional_analysis'] ?? '', 3) as $item) {
            $nutritionalAnalysis[] = [
                'parameter_slug' => $item[0] ?? '',
                'value' => $item[1] ?? '',
                'measure_unit_slug' => $item[2] ?? '',
            ];
        }

        $specifications = [];
        foreach (ProductImportPipeParser::chunk($row['specifications'] ?? '', 4) as $item) {
            $specifications[] = [
                'parameter_slug' => $item[0] ?? '',
                'test_method_slug' => $item[1] ?? '',
                'specification' => $item[2] ?? '',
                'measure_unit_slug' => $item[3] ?? '',
            ];
        }

        return [
            'category_slugs' => ProductImportPipeParser::slugList($row['category_slugs'] ?? ''),
            'handling_spec_slugs' => ProductImportPipeParser::slugList($row['handling_spec_slugs'] ?? ''),
            'application_slugs' => ProductImportPipeParser::slugList($row['application_slugs'] ?? ''),
            'related_product_slugs' => ProductImportPipeParser::slugList($row['related_product_slugs'] ?? ''),
            'packaging' => $packaging,
            'nutritional_analysis' => $nutritionalAnalysis,
            'specifications' => $specifications,
        ];
    }

    /**
     * Normalize AI agent JSON payload to canonical slug payload.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalizeFromAiPayload(array $data): array
    {
        $packaging = [];
        foreach ($data['packaging'] ?? [] as $pkg) {
            $packaging[] = [
                'presentation_index' => (int) ($pkg['presentation_index'] ?? 1),
                'packaging_type_slug' => (string) ($pkg['packaging_type_slug'] ?? ''),
                'quantity_per_pallet' => (int) ($pkg['quantity_per_pallet'] ?? 1),
                'base_price_per_unit' => (float) ($pkg['base_price_per_unit'] ?? 0),
                'volume_tiers' => array_map(static function ($tier) {
                    $mode = self::normalizePricingMode((string) ($tier['pricing_mode'] ?? 'percentage'));

                    return [
                        'tier_name' => (string) ($tier['tier_name'] ?? 'Tier'),
                        'min_quantity' => (int) ($tier['min_quantity'] ?? 0),
                        'max_quantity' => array_key_exists('max_quantity', $tier) && $tier['max_quantity'] !== null
                            ? (int) $tier['max_quantity']
                            : null,
                        'pricing_mode' => $mode,
                        'discount_percentage' => $mode === 'percentage' ? (float) ($tier['discount_percentage'] ?? 0) : 0,
                        'fixed_price' => $mode === 'fixed_price' && ($tier['fixed_price'] ?? '') !== ''
                            ? (float) $tier['fixed_price']
                            : null,
                    ];
                }, $pkg['volume_tiers'] ?? []),
            ];
        }

        $nutritionalAnalysis = [];
        foreach ($data['nutritional_analysis'] ?? [] as $row) {
            $nutritionalAnalysis[] = [
                'parameter_slug' => (string) ($row['parameter_slug'] ?? ''),
                'value' => (string) ($row['value'] ?? ''),
                'measure_unit_slug' => (string) ($row['measure_unit_slug'] ?? ''),
            ];
        }

        $specifications = [];
        foreach ($data['specifications'] ?? [] as $row) {
            $specifications[] = [
                'parameter_slug' => (string) ($row['parameter_slug'] ?? ''),
                'test_method_slug' => (string) ($row['test_method_slug'] ?? ''),
                'specification' => (string) ($row['specification'] ?? ''),
                'measure_unit_slug' => (string) ($row['measure_unit_slug'] ?? ''),
            ];
        }

        return [
            'category_slugs' => array_values($data['category_slugs'] ?? []),
            'handling_spec_slugs' => array_values($data['handling_spec_slugs'] ?? []),
            'application_slugs' => array_values($data['application_slugs'] ?? []),
            'related_product_slugs' => array_values($data['related_product_slugs'] ?? []),
            'packaging' => $packaging,
            'nutritional_analysis' => $nutritionalAnalysis,
            'specifications' => $specifications,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload Canonical slug payload
     * @return list<string>
     */
    public function validateReferences(array $payload, MasterSlugRegistry $registry): array
    {
        $errors = [];

        foreach ($payload['category_slugs'] ?? [] as $catSlug) {
            if (! $registry->isAvailable('category', $catSlug)) {
                $errors[] = "Category slug \"{$catSlug}\" not found.";
            }
        }

        if (($payload['category_slugs'] ?? []) === []) {
            $errors[] = 'At least one category slug is required.';
        }

        foreach ($payload['handling_spec_slugs'] ?? [] as $hsSlug) {
            if (! $registry->isAvailable('handling_spec', $hsSlug)) {
                $errors[] = "Handling spec slug \"{$hsSlug}\" not found.";
            }
        }

        foreach ($payload['application_slugs'] ?? [] as $appSlug) {
            if (! $registry->isAvailable('typical_application', $appSlug)) {
                $errors[] = "Application slug \"{$appSlug}\" not found.";
            }
        }

        foreach ($payload['related_product_slugs'] ?? [] as $relSlug) {
            if (! $registry->isAvailable('product', $relSlug)) {
                $errors[] = "Related product slug \"{$relSlug}\" not found.";
            }
        }

        foreach ($payload['packaging'] ?? [] as $index => $pkg) {
            $typeSlug = $pkg['packaging_type_slug'] ?? '';
            if ($typeSlug !== '' && ! $registry->isAvailable('packaging_type', $typeSlug)) {
                $errors[] = "Packaging type slug \"{$typeSlug}\" not found (packaging index {$index}).";
            }
        }

        foreach ($payload['nutritional_analysis'] ?? [] as $index => $item) {
            $paramSlug = $item['parameter_slug'] ?? '';
            if ($paramSlug !== '' && ! $registry->isAvailable('nutritional_parameter', $paramSlug)) {
                $errors[] = "Nutritional parameter slug \"{$paramSlug}\" not found (row {$index}).";
            }
            $unitSlug = $item['measure_unit_slug'] ?? '';
            if ($unitSlug !== '' && ! $registry->isAvailable('measure_unit', $unitSlug)) {
                $errors[] = "Measure unit slug \"{$unitSlug}\" not found (nutrition row {$index}).";
            }
        }

        foreach ($payload['specifications'] ?? [] as $index => $item) {
            if (($item['parameter_slug'] ?? '') !== '' && ! $registry->isAvailable('parameter', $item['parameter_slug'])) {
                $errors[] = "Parameter slug \"{$item['parameter_slug']}\" not found (spec row {$index}).";
            }
            if (($item['test_method_slug'] ?? '') !== '' && ! $registry->isAvailable('test_method', $item['test_method_slug'])) {
                $errors[] = "Test method slug \"{$item['test_method_slug']}\" not found (spec row {$index}).";
            }
            if (($item['measure_unit_slug'] ?? '') !== '' && ! $registry->isAvailable('measure_unit', $item['measure_unit_slug'])) {
                $errors[] = "Measure unit slug \"{$item['measure_unit_slug']}\" not found (spec row {$index}).";
            }
        }

        $errors = array_merge($errors, $this->validateVolumeTiers($payload));

        return $errors;
    }

    /**
     * Import-only validation: name and packaging are validated upstream; categories are optional.
     *
     * @param  array<string, mixed>  $payload Canonical slug payload
     * @return list<string>
     */
    public function validateImportReferences(array $payload, MasterSlugRegistry $registry): array
    {
        $errors = [];

        foreach ($payload['category_slugs'] ?? [] as $catSlug) {
            if (! $registry->isAvailable('category', $catSlug)) {
                $errors[] = "Category slug \"{$catSlug}\" not found.";
            }
        }

        $packaging = $payload['packaging'] ?? [];
        if ($packaging === []) {
            $errors[] = 'At least one packaging option is required.';
        }

        foreach ($packaging as $index => $pkg) {
            $typeSlug = $pkg['packaging_type_slug'] ?? '';
            if ($typeSlug === '') {
                $errors[] = "Packaging type slug is required (packaging index {$index}).";

                continue;
            }
            if (! $registry->isAvailable('packaging_type', $typeSlug)) {
                $errors[] = "Packaging type slug \"{$typeSlug}\" not found (packaging index {$index}).";
            }
        }

        foreach ($payload['handling_spec_slugs'] ?? [] as $hsSlug) {
            if (! $registry->isAvailable('handling_spec', $hsSlug)) {
                $errors[] = "Handling spec slug \"{$hsSlug}\" not found.";
            }
        }

        foreach ($payload['application_slugs'] ?? [] as $appSlug) {
            if (! $registry->isAvailable('typical_application', $appSlug)) {
                $errors[] = "Application slug \"{$appSlug}\" not found.";
            }
        }

        foreach ($payload['nutritional_analysis'] ?? [] as $index => $item) {
            $paramSlug = $item['parameter_slug'] ?? '';
            if ($paramSlug !== '' && ! $registry->isAvailable('nutritional_parameter', $paramSlug)) {
                $errors[] = "Nutritional parameter slug \"{$paramSlug}\" not found (row {$index}).";
            }
            $unitSlug = $item['measure_unit_slug'] ?? '';
            if ($unitSlug !== '' && ! $registry->isAvailable('measure_unit', $unitSlug)) {
                $errors[] = "Measure unit slug \"{$unitSlug}\" not found (nutrition row {$index}).";
            }
        }

        foreach ($payload['specifications'] ?? [] as $index => $item) {
            if (($item['parameter_slug'] ?? '') !== '' && ! $registry->isAvailable('parameter', $item['parameter_slug'])) {
                $errors[] = "Parameter slug \"{$item['parameter_slug']}\" not found (spec row {$index}).";
            }
            if (($item['test_method_slug'] ?? '') !== '' && ! $registry->isAvailable('test_method', $item['test_method_slug'])) {
                $errors[] = "Test method slug \"{$item['test_method_slug']}\" not found (spec row {$index}).";
            }
            if (($item['measure_unit_slug'] ?? '') !== '' && ! $registry->isAvailable('measure_unit', $item['measure_unit_slug'])) {
                $errors[] = "Measure unit slug \"{$item['measure_unit_slug']}\" not found (spec row {$index}).";
            }
        }

        $errors = array_merge($errors, $this->validateVolumeTiers($payload));

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $payload Canonical slug payload
     * @return array<string, mixed>|null Relations payload for ProductRelationsSync
     */
    public function buildRelationsPayload(array $payload, MasterSlugRegistry $registry): ?array
    {
        $categoryIds = [];
        foreach ($payload['category_slugs'] ?? [] as $slug) {
            $id = $registry->categoryId($slug);
            if ($id === null) {
                return null;
            }
            $categoryIds[] = $id;
        }

        $handlingIds = [];
        foreach ($payload['handling_spec_slugs'] ?? [] as $slug) {
            $id = $registry->handlingSpecId($slug);
            if ($id === null) {
                return null;
            }
            $handlingIds[] = $id;
        }

        $applicationIds = [];
        foreach ($payload['application_slugs'] ?? [] as $slug) {
            $id = $registry->typicalApplicationId($slug);
            if ($id === null) {
                return null;
            }
            $applicationIds[] = $id;
        }

        $packaging = [];
        foreach ($payload['packaging'] ?? [] as $pkg) {
            $typeSlug = $pkg['packaging_type_slug'] ?? '';
            $typeId = $registry->packagingTypeId($typeSlug);
            $presentationIndex = (int) ($pkg['presentation_index'] ?? 0);
            if ($typeId === null || $presentationIndex < 1) {
                return null;
            }

            $volumeTiers = [];
            foreach ($pkg['volume_tiers'] ?? [] as $tier) {
                $mode = self::normalizePricingMode((string) ($tier['pricing_mode'] ?? 'percentage'));
                $volumeTiers[] = [
                    'tier_name' => $tier['tier_name'] ?? 'Tier',
                    'min_quantity' => (int) ($tier['min_quantity'] ?? 0),
                    'max_quantity' => $tier['max_quantity'] ?? null,
                    'pricing_mode' => $mode,
                    'discount_percentage' => $mode === 'percentage' ? (float) ($tier['discount_percentage'] ?? 0) : 0,
                    'fixed_price' => $mode === 'fixed_price' ? ($tier['fixed_price'] ?? null) : null,
                ];
            }

            $packaging[] = [
                'packaging_type_id' => $typeId,
                'quantity_per_pallet' => max(1, (int) ($pkg['quantity_per_pallet'] ?? 1)),
                'base_price_per_unit' => (float) ($pkg['base_price_per_unit'] ?? 0),
                'volume_tiers' => $volumeTiers,
            ];
        }

        $nutritionalAnalysis = [];
        foreach ($payload['nutritional_analysis'] ?? [] as $item) {
            $paramSlug = $item['parameter_slug'] ?? '';
            if ($paramSlug === '') {
                continue;
            }
            $paramId = $registry->nutritionalParameterId($paramSlug);
            if ($paramId === null) {
                return null;
            }
            $unitId = null;
            $unitSlug = $item['measure_unit_slug'] ?? '';
            if ($unitSlug !== '') {
                $unitId = $registry->measureUnitId($unitSlug);
                if ($unitId === null) {
                    return null;
                }
            }
            $nutritionalAnalysis[] = [
                'nutritional_parameter_id' => $paramId,
                'value' => $item['value'] ?? '',
                'measure_unit_id' => $unitId,
            ];
        }

        $specifications = [];
        foreach ($payload['specifications'] ?? [] as $item) {
            $paramSlug = $item['parameter_slug'] ?? '';
            if ($paramSlug === '') {
                continue;
            }
            $parameterId = $registry->parameterId($paramSlug);
            if ($parameterId === null) {
                return null;
            }
            $testMethodId = $registry->testMethodId($item['test_method_slug'] ?? '');
            if ($testMethodId === null) {
                return null;
            }
            $unitId = $registry->measureUnitId($item['measure_unit_slug'] ?? '');
            if ($unitId === null) {
                return null;
            }
            $specifications[] = [
                'parameter_id' => $parameterId,
                'test_method_id' => $testMethodId,
                'specification' => $item['specification'] ?? '',
                'measure_unit_id' => $unitId,
            ];
        }

        $relatedProductIds = [];
        foreach ($payload['related_product_slugs'] ?? [] as $slug) {
            $id = $registry->productId($slug);
            if ($id === null) {
                continue;
            }
            $relatedProductIds[] = $id;
        }

        return [
            'category_ids' => $categoryIds,
            'handling_spec_ids' => $handlingIds,
            'application_ids' => $applicationIds,
            'related_product_ids' => $relatedProductIds,
            'packaging' => $packaging,
            'nutritional_analysis' => $nutritionalAnalysis,
            'specifications' => $specifications,
        ];
    }

    public function uniqueSkuFromSlug(string $slug): string
    {
        $base = strtoupper(str_replace('-', '_', $slug));
        $sku = $base;
        $n = 0;

        while (Product::where('sku', $sku)->exists()) {
            $n++;
            $sku = $base.'_'.$n;
        }

        return $sku;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function parseImportVolumeTierItems(?string $value): array
    {
        $parts = ProductImportPipeParser::split($value);
        if ($parts === []) {
            return [];
        }

        $fieldsPerItem = 7;
        if (count($parts) % 7 !== 0 && count($parts) % 5 === 0) {
            $fieldsPerItem = 5;
        }

        $items = [];
        foreach (ProductImportPipeParser::chunk($value, $fieldsPerItem) as $tier) {
            $mode = $fieldsPerItem === 5
                ? 'percentage'
                : self::normalizePricingMode((string) ($tier[4] ?? 'percentage'));

            $items[] = [
                'presentation_index' => (int) ($tier[0] ?? 0),
                'tier_name' => $tier[1] !== '' ? $tier[1] : 'Tier',
                'min_quantity' => $tier[2] !== '' ? (int) $tier[2] : 0,
                'max_quantity' => $tier[3] !== '' ? (int) $tier[3] : null,
                'pricing_mode' => $mode,
                'discount_percentage' => $mode === 'percentage'
                    ? ($fieldsPerItem === 5
                        ? ($tier[4] !== '' ? (float) $tier[4] : 0)
                        : ($tier[5] !== '' ? (float) $tier[5] : 0))
                    : 0,
                'fixed_price' => $mode === 'fixed_price' && $fieldsPerItem === 7 && ($tier[6] ?? '') !== ''
                    ? (float) $tier[6]
                    : null,
            ];
        }

        return $items;
    }

    private static function normalizePricingMode(string $mode): string
    {
        $normalized = strtolower(trim($mode));

        return match ($normalized) {
            'fixed_price', 'fixed', '$', 'usd', 'price' => 'fixed_price',
            default => 'percentage',
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function validateVolumeTiers(array $payload): array
    {
        $errors = [];

        foreach ($payload['packaging'] ?? [] as $pkgIndex => $pkg) {
            $presentationIndex = (int) ($pkg['presentation_index'] ?? 0);
            $tiers = $pkg['volume_tiers'] ?? [];
            if ($tiers === []) {
                continue;
            }

            $expectedMode = self::normalizePricingMode((string) ($tiers[0]['pricing_mode'] ?? 'percentage'));

            foreach ($tiers as $tierIndex => $tier) {
                $mode = self::normalizePricingMode((string) ($tier['pricing_mode'] ?? $expectedMode));
                if ($mode !== $expectedMode) {
                    $errors[] = "Presentation {$presentationIndex}: all volume tiers must use the same pricing mode (percentage or fixed_price).";
                    break;
                }

                if ($mode === 'fixed_price') {
                    $fixedPrice = $tier['fixed_price'] ?? null;
                    if ($fixedPrice === null || $fixedPrice === '' || (float) $fixedPrice <= 0) {
                        $errors[] = "Presentation {$presentationIndex}, tier ".($tierIndex + 1).': fixed_price is required when pricing_mode is fixed_price.';
                    }
                }
            }
        }

        return $errors;
    }
}
