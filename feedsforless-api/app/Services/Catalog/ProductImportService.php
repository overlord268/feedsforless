<?php

namespace App\Services\Catalog;

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\HandlingSpec;
use App\Domains\Catalog\Models\MeasureUnit;
use App\Domains\Catalog\Models\NutritionalParameter;
use App\Domains\Catalog\Models\PackagingType;
use App\Domains\Catalog\Models\Parameter;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\TestMethod;
use App\Domains\Catalog\Models\TypicalApplication;
use App\Http\Controllers\Api\V1\Admin\AdminCatalogsController;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductImportService
{
    use ProductImportPreviewSupport;

    private readonly ProductSlugPayloadBuilder $payloadBuilder;

    private readonly ProductSlugGenerator $slugGenerator;

    private const DATA_START_ROW = 3;

    public function __construct(
        ?ProductSlugPayloadBuilder $payloadBuilder = null,
        ?ProductSlugGenerator $slugGenerator = null
    ) {
        $this->payloadBuilder = $payloadBuilder ?? new ProductSlugPayloadBuilder;
        $this->slugGenerator = $slugGenerator ?? new ProductSlugGenerator;
    }

    /** @var list<string> */
    private const MASTER_SHEETS = [
        'CATEGORIES',
        'PACKAGING_TYPES',
        'PARAMETERS',
        'TEST_METHODS',
        'MEASURE_UNITS',
        'NUTRITIONAL_PARAMETERS',
        'HANDLING_SPECS',
        'TYPICAL_APPLICATIONS',
    ];

    public function import(string $filePath, bool $dryRun = false, ?array $decisions = null): ProductImportResult
    {
        $this->dryRun = $dryRun;
        $this->decisions = $decisions;
        $this->seenSlugsInFile = [];

        $result = new ProductImportResult;
        $result->dryRun = $dryRun;

        $spreadsheet = IOFactory::load($filePath);
        $registry = new MasterSlugRegistry;
        $registry->bootstrapFromDatabase();

        $masterData = [];
        foreach (self::MASTER_SHEETS as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if ($sheet === null) {
                $result->addError($sheetName, 0, "Sheet \"{$sheetName}\" is missing from the workbook.");

                continue;
            }
            $masterData[$sheetName] = $this->readSheetRows($sheet);
        }

        $productsSheet = $spreadsheet->getSheetByName('PRODUCTS');
        if ($productsSheet === null) {
            $result->addError('PRODUCTS', 0, 'Sheet "PRODUCTS" is missing from the workbook.');

            return $result;
        }

        $productRows = $this->readSheetRows($productsSheet);

        $runner = function () use ($masterData, $productRows, $registry, $result): void {
            if (isset($masterData['CATEGORIES'])) {
                $this->importCategories($masterData['CATEGORIES'], $registry, $result);
            }
            if (isset($masterData['PACKAGING_TYPES'])) {
                $this->importPackagingTypes($masterData['PACKAGING_TYPES'], $registry, $result);
            }
            if (isset($masterData['PARAMETERS'])) {
                $this->importParameters($masterData['PARAMETERS'], $registry, $result);
            }
            if (isset($masterData['TEST_METHODS'])) {
                $this->importTestMethods($masterData['TEST_METHODS'], $registry, $result);
            }
            if (isset($masterData['MEASURE_UNITS'])) {
                $this->importMeasureUnits($masterData['MEASURE_UNITS'], $registry, $result);
            }
            if (isset($masterData['NUTRITIONAL_PARAMETERS'])) {
                $this->importNutritionalParameters($masterData['NUTRITIONAL_PARAMETERS'], $registry, $result);
            }
            if (isset($masterData['HANDLING_SPECS'])) {
                $this->importHandlingSpecs($masterData['HANDLING_SPECS'], $registry, $result);
            }
            if (isset($masterData['TYPICAL_APPLICATIONS'])) {
                $this->importTypicalApplications($masterData['TYPICAL_APPLICATIONS'], $registry, $result);
            }

            $this->importProducts($productRows, $registry, $result);
        };

        if ($dryRun) {
            $runner();
        } else {
            DB::transaction($runner);
            AdminCatalogsController::forgetCache();
        }

        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readSheetRows(Worksheet $sheet): array
    {
        $headers = $this->readHeaders($sheet);
        $rows = [];
        $maxRow = (int) $sheet->getHighestRow();

        for ($rowNum = self::DATA_START_ROW; $rowNum <= $maxRow; $rowNum++) {
            $row = [];
            $hasValue = false;

            foreach ($headers as $header => $column) {
                $value = $sheet->getCell($column.$rowNum)->getValue();
                if ($value !== null && trim((string) $value) !== '') {
                    $hasValue = true;
                }
                $row[$header] = $value !== null ? trim((string) $value) : '';
            }

            if ($hasValue) {
                $row['__row'] = $rowNum;
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function readHeaders(Worksheet $sheet): array
    {
        $headers = [];
        $maxCol = $sheet->getHighestColumn();
        $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxCol);

        for ($i = 1; $i <= $maxColIndex; $i++) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $label = trim((string) $sheet->getCell($column.'1')->getValue());
            if ($label !== '') {
                $headers[$label] = $column;
            }
        }

        return $headers;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importCategories(array $rows, MasterSlugRegistry $registry, ProductImportResult $result): void
    {
        foreach ($this->sortCategoriesByParent($rows) as $row) {
            $rowNum = (int) ($row['__row'] ?? 0);
            $label = $row['label'] ?? '';
            $slug = $row['slug'] ?? '';

            if ($label === '' || $slug === '') {
                $result->addError('CATEGORIES', $rowNum, 'label and slug are required.');
                $this->recordPreview($result, 'category', 'CATEGORIES', $rowNum, $slug ?: '?', $label, 'error', ['Missing label or slug']);

                continue;
            }

            if ($this->isDuplicateInFile('category', $slug, $rowNum, 'CATEGORIES', $result)) {
                continue;
            }

            $parentSlug = trim((string) ($row['parent_slug'] ?? ''));
            if ($parentSlug !== '' && ! $registry->isAvailable('category', $parentSlug)) {
                $msg = "Parent category slug \"{$parentSlug}\" was not found.";
                $result->addError('CATEGORIES', $rowNum, $msg);
                $this->recordPreview($result, 'category', 'CATEGORIES', $rowNum, $slug, $label, 'error', [$msg]);

                continue;
            }

            $existing = Category::where('slug', $slug)->with('parent')->first();
            $incoming = ['label' => $label, 'parent_slug' => $parentSlug];
            $existingData = $existing ? [
                'label' => $existing->label,
                'parent_slug' => $existing->parent?->slug ?? '',
            ] : null;
            $action = $existing ? 'update' : 'create';
            $conflicts = $existing ? $this->diffFields($existingData, $incoming, ['label', 'parent_slug']) : [];
            $recommended = $this->recommendedDecision($action, $conflicts);
            $key = $this->recordPreview($result, 'category', 'CATEGORIES', $rowNum, $slug, $label, $action, $conflicts, $existingData, $incoming);

            $parentId = $parentSlug === '' ? null : $registry->categoryId($parentSlug);

            $this->persistOrPreview($result, $key, $recommended, 'categories', $action, function () use ($existing, $label, $slug, $parentId, $registry): void {
                if ($existing) {
                    $existing->update(['label' => $label, 'parent_id' => $parentId]);
                    $registry->registerCategory($slug, (int) $existing->id);
                } else {
                    $category = Category::create([
                        'label' => $label,
                        'slug' => $slug,
                        'parent_id' => $parentId,
                    ]);
                    $registry->registerCategory($slug, (int) $category->id);
                }
            });

            if ($this->dryRun && $this->resolveDecision($key, $recommended) === 'apply') {
                $registry->markPendingInFile('category', $slug);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function sortCategoriesByParent(array $rows): array
    {
        $bySlug = [];
        foreach ($rows as $row) {
            if (! empty($row['slug'])) {
                $bySlug[$row['slug']] = $row;
            }
        }

        $sorted = [];
        $visited = [];

        $visit = function (string $slug) use (&$visit, &$sorted, &$visited, $bySlug): void {
            if (isset($visited[$slug])) {
                return;
            }
            $visited[$slug] = true;
            $row = $bySlug[$slug] ?? null;
            if ($row === null) {
                return;
            }
            $parent = trim((string) ($row['parent_slug'] ?? ''));
            if ($parent !== '' && isset($bySlug[$parent])) {
                $visit($parent);
            }
            $sorted[] = $row;
        };

        foreach (array_keys($bySlug) as $slug) {
            $visit($slug);
        }

        return $sorted;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importPackagingTypes(array $rows, MasterSlugRegistry $registry, ProductImportResult $result): void
    {
        foreach ($rows as $row) {
            $rowNum = (int) ($row['__row'] ?? 0);
            $name = $row['name'] ?? '';
            $slug = $row['slug'] ?? '';

            if ($name === '' || $slug === '') {
                $result->addError('PACKAGING_TYPES', $rowNum, 'name and slug are required.');
                $this->recordPreview($result, 'packaging_type', 'PACKAGING_TYPES', $rowNum, $slug ?: '?', $name, 'error', ['Missing name or slug']);

                continue;
            }

            if ($this->isDuplicateInFile('packaging_type', $slug, $rowNum, 'PACKAGING_TYPES', $result)) {
                continue;
            }

            $existing = PackagingType::findByImportSlug($slug, $name, 'name');
            $incoming = ['name' => $name, 'slug' => $slug];
            $existingData = $existing ? ['name' => $existing->name, 'slug' => $existing->slug ?? ''] : null;
            $action = $existing ? 'update' : 'create';
            $conflicts = $existing ? $this->diffFields($existingData, $incoming, ['name', 'slug']) : [];
            $recommended = $this->recommendedDecision($action, $conflicts);
            $key = $this->recordPreview($result, 'packaging_type', 'PACKAGING_TYPES', $rowNum, $slug, $name, $action, $conflicts, $existingData, $incoming);

            $this->persistOrPreview($result, $key, $recommended, 'packaging_types', $action, function () use ($existing, $incoming, $slug, $registry): void {
                if ($existing) {
                    $existing->update($incoming);
                    $registry->registerPackagingType($slug, (int) $existing->id);
                } else {
                    $model = PackagingType::create($incoming);
                    $registry->registerPackagingType($slug, (int) $model->id);
                }
            });

            if ($this->dryRun && $this->resolveDecision($key, $recommended) === 'apply') {
                $registry->markPendingInFile('packaging_type', $slug);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importParameters(array $rows, MasterSlugRegistry $registry, ProductImportResult $result): void
    {
        foreach ($rows as $row) {
            $rowNum = (int) ($row['__row'] ?? 0);
            $label = $row['label'] ?? '';
            $slug = $row['slug'] ?? '';

            if ($label === '' || $slug === '') {
                $result->addError('PARAMETERS', $rowNum, 'label and slug are required.');
                $this->recordPreview($result, 'parameter', 'PARAMETERS', $rowNum, $slug ?: '?', $label, 'error', ['Missing label or slug']);

                continue;
            }

            if ($this->isDuplicateInFile('parameter', $slug, $rowNum, 'PARAMETERS', $result)) {
                continue;
            }

            $existing = Parameter::findByImportSlug($slug, $label);
            $incoming = ['label' => $label, 'slug' => $slug, 'type' => ($row['type'] ?? '') ?: null];
            $existingData = $existing ? ['label' => $existing->label, 'slug' => $existing->slug ?? '', 'type' => $existing->type] : null;
            $action = $existing ? 'update' : 'create';
            $conflicts = $existing ? $this->diffFields($existingData, $incoming, ['label', 'slug', 'type']) : [];
            $recommended = $this->recommendedDecision($action, $conflicts);
            $key = $this->recordPreview($result, 'parameter', 'PARAMETERS', $rowNum, $slug, $label, $action, $conflicts, $existingData, $incoming);

            $this->persistOrPreview($result, $key, $recommended, 'parameters', $action, function () use ($existing, $incoming, $slug, $registry): void {
                if ($existing) {
                    $existing->update($incoming);
                    $registry->registerParameter($slug, (int) $existing->id);
                } else {
                    $model = Parameter::create($incoming);
                    $registry->registerParameter($slug, (int) $model->id);
                }
            });

            if ($this->dryRun && $this->resolveDecision($key, $recommended) === 'apply') {
                $registry->markPendingInFile('parameter', $slug);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importTestMethods(array $rows, MasterSlugRegistry $registry, ProductImportResult $result): void
    {
        foreach ($rows as $row) {
            $rowNum = (int) ($row['__row'] ?? 0);
            $label = $row['label'] ?? '';
            $slug = $row['slug'] ?? '';

            if ($label === '' || $slug === '') {
                $result->addError('TEST_METHODS', $rowNum, 'label and slug are required.');
                $this->recordPreview($result, 'test_method', 'TEST_METHODS', $rowNum, $slug ?: '?', $label, 'error', ['Missing label or slug']);

                continue;
            }

            if ($this->isDuplicateInFile('test_method', $slug, $rowNum, 'TEST_METHODS', $result)) {
                continue;
            }

            $existing = TestMethod::findByImportSlug($slug, $label);
            $incoming = ['label' => $label, 'slug' => $slug];
            $existingData = $existing ? ['label' => $existing->label, 'slug' => $existing->slug ?? ''] : null;
            $action = $existing ? 'update' : 'create';
            $conflicts = $existing ? $this->diffFields($existingData, $incoming, ['label', 'slug']) : [];
            $recommended = $this->recommendedDecision($action, $conflicts);
            $key = $this->recordPreview($result, 'test_method', 'TEST_METHODS', $rowNum, $slug, $label, $action, $conflicts, $existingData, $incoming);

            $this->persistOrPreview($result, $key, $recommended, 'test_methods', $action, function () use ($existing, $incoming, $slug, $registry): void {
                if ($existing) {
                    $existing->update($incoming);
                    $registry->registerTestMethod($slug, (int) $existing->id);
                } else {
                    $model = TestMethod::create($incoming);
                    $registry->registerTestMethod($slug, (int) $model->id);
                }
            });

            if ($this->dryRun && $this->resolveDecision($key, $recommended) === 'apply') {
                $registry->markPendingInFile('test_method', $slug);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importMeasureUnits(array $rows, MasterSlugRegistry $registry, ProductImportResult $result): void
    {
        foreach ($rows as $row) {
            $rowNum = (int) ($row['__row'] ?? 0);
            $label = $row['label'] ?? '';
            $slug = $row['slug'] ?? '';

            if ($label === '' || $slug === '') {
                $result->addError('MEASURE_UNITS', $rowNum, 'label and slug are required.');
                $this->recordPreview($result, 'measure_unit', 'MEASURE_UNITS', $rowNum, $slug ?: '?', $label, 'error', ['Missing label or slug']);

                continue;
            }

            if ($this->isDuplicateInFile('measure_unit', $slug, $rowNum, 'MEASURE_UNITS', $result)) {
                continue;
            }

            $existing = MeasureUnit::findByImportSlug($slug, $label);
            $incoming = [
                'label' => $label,
                'slug' => $slug,
                'notation' => ($row['notation'] ?? '') ?: null,
            ];
            $existingData = $existing ? ['label' => $existing->label, 'slug' => $existing->slug ?? '', 'notation' => $existing->notation] : null;
            $action = $existing ? 'update' : 'create';
            $conflicts = $existing ? $this->diffFields($existingData, $incoming, ['label', 'slug', 'notation']) : [];
            $recommended = $this->recommendedDecision($action, $conflicts);
            $key = $this->recordPreview($result, 'measure_unit', 'MEASURE_UNITS', $rowNum, $slug, $label, $action, $conflicts, $existingData, $incoming);

            $this->persistOrPreview($result, $key, $recommended, 'measure_units', $action, function () use ($existing, $incoming, $slug, $registry): void {
                if ($existing) {
                    $existing->update($incoming);
                    $registry->registerMeasureUnit($slug, (int) $existing->id);
                    if ($incoming['notation']) {
                        $registry->registerMeasureUnit($incoming['notation'], (int) $existing->id);
                    }
                } else {
                    $model = MeasureUnit::create($incoming);
                    $registry->registerMeasureUnit($slug, (int) $model->id);
                    if ($incoming['notation']) {
                        $registry->registerMeasureUnit($incoming['notation'], (int) $model->id);
                    }
                }
            });

            if ($this->dryRun && $this->resolveDecision($key, $recommended) === 'apply') {
                $registry->markPendingInFile('measure_unit', $slug);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importNutritionalParameters(array $rows, MasterSlugRegistry $registry, ProductImportResult $result): void
    {
        foreach ($rows as $row) {
            $rowNum = (int) ($row['__row'] ?? 0);
            $label = $row['label'] ?? '';
            $slug = $row['slug'] ?? '';

            if ($label === '' || $slug === '') {
                $result->addError('NUTRITIONAL_PARAMETERS', $rowNum, 'label and slug are required.');
                $this->recordPreview($result, 'nutritional_parameter', 'NUTRITIONAL_PARAMETERS', $rowNum, $slug ?: '?', $label, 'error', ['Missing label or slug']);

                continue;
            }

            if ($this->isDuplicateInFile('nutritional_parameter', $slug, $rowNum, 'NUTRITIONAL_PARAMETERS', $result)) {
                continue;
            }

            $existing = NutritionalParameter::findByImportSlug($slug, $label);
            $incoming = [
                'label' => $label,
                'slug' => $slug,
                'notation' => ($row['notation'] ?? '') ?: null,
            ];
            $existingData = $existing ? ['label' => $existing->label, 'slug' => $existing->slug ?? '', 'notation' => $existing->notation] : null;
            $action = $existing ? 'update' : 'create';
            $conflicts = $existing ? $this->diffFields($existingData, $incoming, ['label', 'slug', 'notation']) : [];
            $recommended = $this->recommendedDecision($action, $conflicts);
            $key = $this->recordPreview($result, 'nutritional_parameter', 'NUTRITIONAL_PARAMETERS', $rowNum, $slug, $label, $action, $conflicts, $existingData, $incoming);

            $this->persistOrPreview($result, $key, $recommended, 'nutritional_parameters', $action, function () use ($existing, $incoming, $slug, $registry): void {
                if ($existing) {
                    $existing->update($incoming);
                    $registry->registerNutritionalParameter($slug, (int) $existing->id);
                } else {
                    $model = NutritionalParameter::create($incoming);
                    $registry->registerNutritionalParameter($slug, (int) $model->id);
                }
            });

            if ($this->dryRun && $this->resolveDecision($key, $recommended) === 'apply') {
                $registry->markPendingInFile('nutritional_parameter', $slug);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importHandlingSpecs(array $rows, MasterSlugRegistry $registry, ProductImportResult $result): void
    {
        foreach ($rows as $row) {
            $rowNum = (int) ($row['__row'] ?? 0);
            $label = $row['label'] ?? '';
            $slug = $row['slug'] ?? '';
            $requirement = $row['requirement'] ?? '';

            if ($label === '' || $slug === '' || $requirement === '') {
                $result->addError('HANDLING_SPECS', $rowNum, 'label, slug, and requirement are required.');
                $this->recordPreview($result, 'handling_spec', 'HANDLING_SPECS', $rowNum, $slug ?: '?', $label, 'error', ['Missing label, slug, or requirement']);

                continue;
            }

            if ($this->isDuplicateInFile('handling_spec', $slug, $rowNum, 'HANDLING_SPECS', $result)) {
                continue;
            }

            $existing = HandlingSpec::findByImportSlug($slug, $label);
            $incoming = ['label' => $label, 'slug' => $slug, 'requirement' => $requirement];
            $existingData = $existing ? ['label' => $existing->label, 'slug' => $existing->slug ?? '', 'requirement' => $existing->requirement] : null;
            $action = $existing ? 'update' : 'create';
            $conflicts = $existing ? $this->diffFields($existingData, $incoming, ['label', 'slug', 'requirement']) : [];
            $recommended = $this->recommendedDecision($action, $conflicts);
            $key = $this->recordPreview($result, 'handling_spec', 'HANDLING_SPECS', $rowNum, $slug, $label, $action, $conflicts, $existingData, $incoming);

            $this->persistOrPreview($result, $key, $recommended, 'handling_specs', $action, function () use ($existing, $incoming, $slug, $registry): void {
                if ($existing) {
                    $existing->update($incoming);
                    $registry->registerHandlingSpec($slug, (int) $existing->id);
                } else {
                    $model = HandlingSpec::create($incoming);
                    $registry->registerHandlingSpec($slug, (int) $model->id);
                }
            });

            if ($this->dryRun && $this->resolveDecision($key, $recommended) === 'apply') {
                $registry->markPendingInFile('handling_spec', $slug);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importTypicalApplications(array $rows, MasterSlugRegistry $registry, ProductImportResult $result): void
    {
        foreach ($rows as $row) {
            $rowNum = (int) ($row['__row'] ?? 0);
            $label = $row['label'] ?? '';
            $slug = $row['slug'] ?? '';

            if ($label === '' || $slug === '') {
                $result->addError('TYPICAL_APPLICATIONS', $rowNum, 'label and slug are required.');
                $this->recordPreview($result, 'typical_application', 'TYPICAL_APPLICATIONS', $rowNum, $slug ?: '?', $label, 'error', ['Missing label or slug']);

                continue;
            }

            if ($this->isDuplicateInFile('typical_application', $slug, $rowNum, 'TYPICAL_APPLICATIONS', $result)) {
                continue;
            }

            $existing = TypicalApplication::findByImportSlug($slug, $label);
            $incoming = [
                'label' => $label,
                'slug' => $slug,
                'description' => ($row['description'] ?? '') ?: null,
            ];
            $existingData = $existing ? ['label' => $existing->label, 'slug' => $existing->slug ?? '', 'description' => $existing->description] : null;
            $action = $existing ? 'update' : 'create';
            $conflicts = $existing ? $this->diffFields($existingData, $incoming, ['label', 'slug', 'description']) : [];
            $recommended = $this->recommendedDecision($action, $conflicts);
            $key = $this->recordPreview($result, 'typical_application', 'TYPICAL_APPLICATIONS', $rowNum, $slug, $label, $action, $conflicts, $existingData, $incoming);

            $this->persistOrPreview($result, $key, $recommended, 'typical_applications', $action, function () use ($existing, $incoming, $slug, $registry): void {
                if ($existing) {
                    $existing->update($incoming);
                    $registry->registerTypicalApplication($slug, (int) $existing->id);
                } else {
                    $model = TypicalApplication::create($incoming);
                    $registry->registerTypicalApplication($slug, (int) $model->id);
                }
            });

            if ($this->dryRun && $this->resolveDecision($key, $recommended) === 'apply') {
                $registry->markPendingInFile('typical_application', $slug);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importProducts(array $rows, MasterSlugRegistry $registry, ProductImportResult $result): void
    {
        foreach ($rows as $row) {
            $slug = $this->resolveImportProductSlug($row);
            if ($slug !== null) {
                $registry->markPendingInFile('product', $slug);
            }
        }

        /** @var list<array{row: array<string, mixed>, row_num: int, attributes: array<string, mixed>, related_slugs: list<string>}> $pending */
        $pending = [];

        foreach ($rows as $row) {
            $rowNum = (int) ($row['__row'] ?? 0);
            $name = trim((string) ($row['name'] ?? ''));

            $slug = $this->resolveImportProductSlug($row);
            if ($slug === null) {
                $result->addError('PRODUCTS', $rowNum, 'name is required.');
                $this->recordPreview($result, 'product', 'PRODUCTS', $rowNum, '?', $name, 'error', ['Missing name']);

                continue;
            }

            if ($this->isDuplicateInFile('product', $slug, $rowNum, 'PRODUCTS', $result)) {
                continue;
            }

            $stockStatus = ($row['stock_status'] ?? '') ?: 'in_stock';
            if (! in_array($stockStatus, ['in_stock', 'out_of_stock', 'backorder', 'call'], true)) {
                $result->addError('PRODUCTS', $rowNum, "Invalid stock_status \"{$stockStatus}\".");
                $this->recordPreview($result, 'product', 'PRODUCTS', $rowNum, $slug, $name, 'error', ["Invalid stock_status \"{$stockStatus}\""]);

                continue;
            }

            $canonical = $this->payloadBuilder->normalizeFromImportRow($row);
            $refErrors = $this->payloadBuilder->validateImportReferences($canonical, $registry);
            if ($refErrors !== []) {
                foreach ($refErrors as $msg) {
                    $result->addError('PRODUCTS', $rowNum, $msg);
                }
                $this->recordPreview($result, 'product', 'PRODUCTS', $rowNum, $slug, $name, 'error', $refErrors);

                continue;
            }

            $relatedSlugs = ProductImportPipeParser::slugList($row['related_product_slugs'] ?? '');

            $attributes = [
                'name' => $name,
                'slug' => $slug,
                'grade' => ($row['grade'] ?? '') ?: null,
                'base_price_ref' => ($row['base_price_ref'] ?? '') !== '' ? (float) $row['base_price_ref'] : null,
                'description' => ($row['description'] ?? '') ?: '',
                'status' => 'draft',
                'stock_status' => $stockStatus,
                'availability' => ($row['availability'] ?? '') ?: null,
                'lead_time' => $this->leadTimeDate($row['lead_time_days'] ?? null),
                'max_lead_time' => $this->leadTimeDate($row['max_lead_time_days'] ?? null),
                'origin_address' => ($row['origin_address'] ?? '') ?: null,
                'shelf_life_template' => ($row['shelf_life_template'] ?? '') ?: null,
                'market_trends_link' => ($row['market_trends_link'] ?? '') ?: null,
                'tds_document_path' => ($row['tds_document_url'] ?? '') ?: null,
                'sds_document_path' => ($row['sds_document_url'] ?? '') ?: null,
                'coa_document_path' => ($row['coa_document_url'] ?? '') ?: null,
            ];

            $existing = $this->findProductBySlug($slug);
            $compareFields = ['name', 'grade', 'base_price_ref', 'status', 'stock_status', 'availability'];
            $incomingCompare = array_intersect_key($attributes, array_flip($compareFields));
            $existingData = null;
            if ($existing) {
                $existingData = $existing->only($compareFields);
            }

            $action = $existing ? 'update' : 'create';
            $conflicts = $existing ? $this->diffFields($existingData, $incomingCompare, $compareFields) : [];
            $details = $this->productPreviewDetails($row, $relatedSlugs, $registry);
            $recommended = $this->recommendedDecision($action, $conflicts);
            $key = $this->recordPreview(
                $result,
                'product',
                'PRODUCTS',
                $rowNum,
                $slug,
                $name,
                $action,
                $conflicts,
                $existingData,
                $incomingCompare,
                $details
            );

            $pending[] = [
                'row' => $row,
                'row_num' => $rowNum,
                'attributes' => $attributes,
                'related_slugs' => $relatedSlugs,
                'preview_key' => $key,
                'recommended' => $recommended,
            ];
        }

        foreach ($pending as $item) {
            $slug = $item['attributes']['slug'];
            $key = $item['preview_key'];
            $recommended = $item['recommended'];
            $action = $this->findProductBySlug($slug) !== null ? 'update' : 'create';

            try {
                $this->persistOrPreview($result, $key, $recommended, 'products', $action, function () use ($item, $slug, $registry): void {
                    $canonical = $this->payloadBuilder->normalizeFromImportRow($item['row']);
                    $built = $this->payloadBuilder->buildRelationsPayload($canonical, $registry);
                    if ($built === null) {
                        throw new \RuntimeException("Could not build relations for product \"{$slug}\".");
                    }

                    $relations = $built;
                    $relations['related_product_ids'] = [];

                    $existing = $this->findProductBySlug($slug);
                    if ($existing) {
                        if ($existing->trashed()) {
                            $existing->restore();
                        }
                        $existing->update($item['attributes']);
                        ProductRelationsSync::sync($existing, $relations);
                        $registry->registerProduct($slug, (int) $existing->id);
                    } else {
                        $attributes = $item['attributes'];
                        $attributes['sku'] = null;
                        try {
                            $product = Product::create($attributes);
                        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                            $existing = $this->findProductBySlug($slug);
                            if ($existing === null) {
                                throw new \RuntimeException("Product slug \"{$slug}\" already exists but could not be loaded for update.");
                            }
                            if ($existing->trashed()) {
                                $existing->restore();
                            }
                            $existing->update($item['attributes']);
                            ProductRelationsSync::sync($existing, $relations);
                            $registry->registerProduct($slug, (int) $existing->id);

                            return;
                        }
                        ProductRelationsSync::sync($product, $relations);
                        $registry->registerProduct($slug, (int) $product->id);
                    }
                });
            } catch (\RuntimeException $e) {
                $result->addError('PRODUCTS', $item['row_num'], $e->getMessage());
            }

            if ($this->dryRun && $this->resolveDecision($key, $recommended) === 'apply') {
                $registry->markPendingInFile('product', $slug);
            }
        }

        foreach ($pending as $item) {
            if ($item['related_slugs'] === []) {
                continue;
            }

            $slug = $item['attributes']['slug'];
            $key = $item['preview_key'];
            $recommended = $item['recommended'];

            if (! $this->shouldApply($key, $recommended)) {
                continue;
            }

            $productId = $registry->productId($slug);
            if ($productId === null) {
                continue;
            }

            $relatedIds = [];
            $skippedRelated = [];
            foreach ($item['related_slugs'] as $relatedSlug) {
                $relatedId = $registry->productId($relatedSlug);
                if ($relatedId === null) {
                    $skippedRelated[] = $relatedSlug;

                    continue;
                }
                $relatedIds[] = $relatedId;
            }

            if ($relatedIds === []) {
                continue;
            }

            if (! $this->dryRun) {
                $product = Product::find($productId);
                if ($product) {
                    ProductRelationsSync::sync($product, ['related_product_ids' => $relatedIds]);
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveImportProductSlug(array $row): ?string
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            return null;
        }

        $slug = trim((string) ($row['slug'] ?? ''));

        return $slug !== '' ? $slug : $this->slugGenerator->uniqueFromName($name);
    }

    private function findProductBySlug(string $slug): ?Product
    {
        return Product::withTrashed()->where('slug', $slug)->first();
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $relatedSlugs
     */
    private function productPreviewDetails(array $row, array $relatedSlugs, MasterSlugRegistry $registry): string
    {
        $categories = implode(', ', ProductImportPipeParser::slugList($row['category_slugs'] ?? '')) ?: '—';
        $packagingCount = count(array_filter(
            ProductImportPipeParser::chunk($row['packaging'] ?? '', 4),
            static fn (array $item): bool => trim((string) ($item[1] ?? '')) !== ''
        ));
        $specCount = count(ProductImportPipeParser::chunk($row['specifications'] ?? '', 4));
        $nutritionCount = count(ProductImportPipeParser::chunk($row['nutritional_analysis'] ?? '', 3));

        if ($relatedSlugs === []) {
            $related = '—';
        } else {
            $relatedParts = [];
            foreach ($relatedSlugs as $relatedSlug) {
                if ($registry->isAvailable('product', $relatedSlug)) {
                    $relatedParts[] = $relatedSlug;
                } else {
                    $relatedParts[] = "{$relatedSlug} (link skipped — import that product first)";
                }
            }
            $related = implode(', ', $relatedParts);
        }

        return "Categories: {$categories} | Packaging: {$packagingCount} | Specs: {$specCount} | Nutrition: {$nutritionCount} | Related: {$related}";
    }

    private function leadTimeDate(?string $days): ?string
    {
        if ($days === null || trim($days) === '') {
            return null;
        }

        $value = (int) $days;

        return $value > 0 ? now()->addDays($value)->toDateString() : null;
    }

}
