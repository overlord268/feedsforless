<?php

namespace App\Services\Catalog;

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Product;
use InvalidArgumentException;

class FflSkuGenerator
{
    public function __construct(
        private readonly FflSkuCodeDeriver $codeDeriver = new FflSkuCodeDeriver,
        private readonly FflSkuGradeRepository $gradeRepository = new FflSkuGradeRepository
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function generate(Category $category, string $productName, ?string $gradeSpec): string
    {
        $cat = $this->codeDeriver->catFromCategory($category);
        $prod = $this->codeDeriver->prodFromProductName($productName);
        $grade = $this->parseGradeSpec($gradeSpec);

        $prefix = config('ffl_sku.prefix', 'FFL');

        return "{$prefix}-{$cat}-{$prod}-{$grade}";
    }

    /**
     * @param  list<int>  $categoryIds
     *
     * @throws InvalidArgumentException
     */
    public function assignUniqueSkuFromCategories(
        array $categoryIds,
        string $productName,
        ?string $gradeSpec,
        ?int $excludeProductId = null
    ): string {
        $category = $this->primaryCategoryFromIds($categoryIds);
        $sku = $this->generate($category, $productName, $gradeSpec);

        $exists = Product::query()
            ->where('sku', $sku)
            ->when($excludeProductId !== null, fn ($q) => $q->where('id', '!=', $excludeProductId))
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException("FFL SKU \"{$sku}\" is already assigned to another product.");
        }

        return $sku;
    }

    /**
     * @param  list<int>  $categoryIds
     *
     * @throws InvalidArgumentException
     */
    public function primaryCategoryFromIds(array $categoryIds): Category
    {
        if ($categoryIds === []) {
            throw new InvalidArgumentException('At least one category is required to generate an FFL SKU.');
        }

        $category = Category::query()->with('parent.parent')->find($categoryIds[0]);
        if ($category === null) {
            throw new InvalidArgumentException('Category not found for FFL SKU generation.');
        }

        return $category;
    }

    /**
     * Resolve SKU grade suffix from the product Grade field via the registered grades catalog.
     */
    public function parseGradeSpec(?string $gradeSpec): string
    {
        $pending = config('ffl_sku.pending_grade', 'PENDING');

        if ($gradeSpec === null || trim($gradeSpec) === '') {
            return $pending;
        }

        $registered = $this->gradeRepository->resolveSkuCode($gradeSpec);
        if ($registered !== null) {
            return $registered;
        }

        if ($this->gradeRepository->all()->isEmpty()) {
            return $this->parseGradeSpecFallback(
                $this->gradeRepository->normalizeGradeSpec($gradeSpec),
                $pending
            );
        }

        return $pending;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function skuForProduct(Product $product): string
    {
        $categoryIds = $product->categories()->pluck('categories.id')->all();

        return $this->assignUniqueSkuFromCategories(
            $categoryIds,
            $product->name,
            $product->grade,
            $product->id
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function generateForProduct(Product $product): string
    {
        $category = $this->primaryCategoryFromIds(
            $product->categories()->pluck('categories.id')->all()
        );

        return $this->generate($category, $product->name, $product->grade);
    }

    /** @internal Used only when grades catalog table is not seeded (e.g. unit tests). */
    private function parseGradeSpecFallback(string $spec, string $pending): string
    {
        if (preg_match('/70\s*\/\s*30/i', $spec)) {
            return '7030';
        }
        if (preg_match('/HR\s*95/i', $spec)) {
            return 'HR95';
        }
        if (preg_match('/feed\s*grade/i', $spec)) {
            return 'FG';
        }
        if (preg_match('/poultry(\s*grade)?/i', $spec)) {
            return 'PLT';
        }
        if (preg_match('/lo\s*fluor/i', $spec)) {
            return 'LOF';
        }
        if (preg_match('/standard(\s*grade)?/i', $spec)) {
            return 'STD';
        }
        if (preg_match('/mm/i', $spec) && preg_match('/(\d+(?:\.\d+)?)\s*%/', $spec, $pctMatch)) {
            $pct = (int) round((float) $pctMatch[1]);
            $isFine = (bool) preg_match('/(?:^|[\s(])0\.3(?:\D|$)/', $spec);

            return $pct.($isFine ? 'F' : 'C');
        }
        if (preg_match('/(\d+(?:\.\d+)?)\s*%/', $spec, $pctMatch)) {
            return (string) (int) round(((float) $pctMatch[1]) * 10);
        }

        return $pending;
    }
}
