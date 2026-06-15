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

class MasterSlugRegistry
{
    /** @var array<string, int> */
    private array $categories = [];

    /** @var array<string, int> */
    private array $packagingTypes = [];

    /** @var array<string, int> */
    private array $parameters = [];

    /** @var array<string, int> */
    private array $testMethods = [];

    /** @var array<string, int> */
    private array $measureUnits = [];

    /** @var array<string, int> */
    private array $nutritionalParameters = [];

    /** @var array<string, int> */
    private array $handlingSpecs = [];

    /** @var array<string, int> */
    private array $typicalApplications = [];

    /** @var array<string, int> */
    private array $products = [];

    /** @var array<string, array<string, true>> Slugs that will exist after this import (file + DB). */
    private array $pendingInFile = [];

    public function markPendingInFile(string $entity, string $slug): void
    {
        $this->pendingInFile[$entity][$slug] = true;
    }

    public function isAvailable(string $entity, string $slug): bool
    {
        if (isset($this->pendingInFile[$entity][$slug])) {
            return true;
        }

        return match ($entity) {
            'category' => $this->categoryId($slug) !== null,
            'packaging_type' => $this->packagingTypeId($slug) !== null,
            'parameter' => $this->parameterId($slug) !== null,
            'test_method' => $this->testMethodId($slug) !== null,
            'measure_unit' => $this->measureUnitId($slug) !== null,
            'nutritional_parameter' => $this->nutritionalParameterId($slug) !== null,
            'handling_spec' => $this->handlingSpecId($slug) !== null,
            'typical_application' => $this->typicalApplicationId($slug) !== null,
            'product' => $this->productId($slug) !== null,
            default => false,
        };
    }

    public function bootstrapFromDatabase(): void
    {
        foreach (Category::all(['id', 'slug']) as $row) {
            $this->categories[$row->slug] = (int) $row->id;
        }

        foreach (Product::withTrashed()->get(['id', 'slug']) as $row) {
            if ($row->slug) {
                $this->products[$row->slug] = (int) $row->id;
            }
        }

        foreach (PackagingType::all(['id', 'slug']) as $row) {
            if ($row->slug) {
                $this->packagingTypes[$row->slug] = (int) $row->id;
            }
        }

        foreach (Parameter::all(['id', 'slug']) as $row) {
            if ($row->slug) {
                $this->parameters[$row->slug] = (int) $row->id;
            }
        }

        foreach (TestMethod::all(['id', 'slug']) as $row) {
            if ($row->slug) {
                $this->testMethods[$row->slug] = (int) $row->id;
            }
        }

        foreach (MeasureUnit::all(['id', 'slug', 'notation']) as $row) {
            if ($row->slug) {
                $this->measureUnits[$row->slug] = (int) $row->id;
            }
            if ($row->notation) {
                $this->measureUnits[$row->notation] = (int) $row->id;
            }
        }

        foreach (NutritionalParameter::all(['id', 'slug']) as $row) {
            if ($row->slug) {
                $this->nutritionalParameters[$row->slug] = (int) $row->id;
            }
        }

        foreach (HandlingSpec::all(['id', 'slug']) as $row) {
            if ($row->slug) {
                $this->handlingSpecs[$row->slug] = (int) $row->id;
            }
        }

        foreach (TypicalApplication::all(['id', 'slug']) as $row) {
            if ($row->slug) {
                $this->typicalApplications[$row->slug] = (int) $row->id;
            }
        }
    }

    public function registerCategory(string $slug, int $id): void
    {
        $this->categories[$slug] = $id;
    }

    public function categoryId(string $slug): ?int
    {
        return $this->categories[$slug] ?? null;
    }

    public function registerPackagingType(string $slug, int $id): void
    {
        $this->packagingTypes[$slug] = $id;
    }

    public function packagingTypeId(string $slug): ?int
    {
        return $this->packagingTypes[$slug] ?? null;
    }

    public function registerParameter(string $slug, int $id): void
    {
        $this->parameters[$slug] = $id;
    }

    public function parameterId(string $slug): ?int
    {
        return $this->parameters[$slug] ?? null;
    }

    public function registerTestMethod(string $slug, int $id): void
    {
        $this->testMethods[$slug] = $id;
    }

    public function testMethodId(string $slug): ?int
    {
        return $this->testMethods[$slug] ?? null;
    }

    public function registerMeasureUnit(string $slug, int $id): void
    {
        $this->measureUnits[$slug] = $id;
    }

    public function measureUnitId(string $slug): ?int
    {
        return $this->measureUnits[$slug] ?? null;
    }

    public function registerNutritionalParameter(string $slug, int $id): void
    {
        $this->nutritionalParameters[$slug] = $id;
    }

    public function nutritionalParameterId(string $slug): ?int
    {
        return $this->nutritionalParameters[$slug] ?? null;
    }

    public function registerHandlingSpec(string $slug, int $id): void
    {
        $this->handlingSpecs[$slug] = $id;
    }

    public function handlingSpecId(string $slug): ?int
    {
        return $this->handlingSpecs[$slug] ?? null;
    }

    public function registerTypicalApplication(string $slug, int $id): void
    {
        $this->typicalApplications[$slug] = $id;
    }

    public function typicalApplicationId(string $slug): ?int
    {
        return $this->typicalApplications[$slug] ?? null;
    }

    public function registerProduct(string $slug, int $id): void
    {
        $this->products[$slug] = $id;
    }

    public function productId(string $slug): ?int
    {
        return $this->products[$slug] ?? null;
    }
}
