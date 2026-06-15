<?php

namespace App\Services\Catalog;

use App\Models\FflSkuGrade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class FflSkuGradeRepository
{
    private const CACHE_KEY = 'ffl_sku.grades';

    private const CACHE_TTL = 3600;

    /**
     * @return Collection<int, FflSkuGrade>
     */
    public function all(): Collection
    {
        if (! Schema::hasTable('ffl_sku_grades')) {
            return collect();
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): Collection {
            return FflSkuGrade::query()->orderBy('grade_spec')->get();
        });
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function normalizeGradeSpec(string $gradeSpec): string
    {
        $spec = preg_replace('/[\x{2010}-\x{2015}\x{2212}]/u', '-', $gradeSpec) ?? $gradeSpec;
        $spec = trim($spec);

        if (preg_match('/^(\d+(?:\.\d+)?)\s*(%?)$/u', $spec, $match)) {
            $numeric = (float) $match[1];
            $normalized = rtrim(rtrim(sprintf('%.10F', $numeric), '0'), '.');
            $suffix = str_contains($spec, '%') ? '%' : ($match[2] !== '' ? '%' : '');

            return $normalized.$suffix;
        }

        return $spec;
    }

    public function resolveSkuCode(?string $gradeSpec): ?string
    {
        if ($gradeSpec === null || trim($gradeSpec) === '') {
            return null;
        }

        $needle = $this->normalizeGradeSpec($gradeSpec);

        foreach ($this->all() as $grade) {
            if ($this->normalizeGradeSpec($grade->grade_spec) === $needle) {
                return strtoupper($grade->sku_code);
            }
        }

        return null;
    }
}
