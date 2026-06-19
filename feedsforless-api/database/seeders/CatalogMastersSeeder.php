<?php

namespace Database\Seeders;

use App\Domains\Catalog\Models\HandlingSpec;
use App\Domains\Catalog\Models\MeasureUnit;
use App\Domains\Catalog\Models\NutritionalParameter;
use App\Domains\Catalog\Models\PackagingType;
use App\Domains\Catalog\Models\Parameter;
use App\Domains\Catalog\Models\TestMethod;
use App\Domains\Catalog\Models\TypicalApplication;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogMastersSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Percent', 'PPM', 'mg/kg'] as $label) {
            MeasureUnit::firstOrCreate(
                ['slug' => Str::slug($label)],
                ['label' => $label, 'notation' => $label === 'Percent' ? '%' : $label],
            );
        }

        foreach (['Phosphorus (P)', 'Calcium (Ca)', 'Moisture'] as $label) {
            Parameter::firstOrCreate(
                ['slug' => Str::slug($label)],
                ['label' => $label, 'type' => 'numeric'],
            );
        }

        foreach (['AOAC Official Method', 'NIR Analysis', 'Lab Assay'] as $label) {
            TestMethod::firstOrCreate(
                ['slug' => Str::slug($label)],
                ['label' => $label],
            );
        }

        foreach (['Bulk Bag', 'Super Sack', 'Pallet'] as $name) {
            PackagingType::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }

        foreach (['Crude Protein equivalent', 'Phosphorus', 'Calcium'] as $label) {
            NutritionalParameter::firstOrCreate(
                ['slug' => Str::slug($label)],
                ['label' => $label, 'notation' => $label === 'Phosphorus' ? 'P' : ($label === 'Calcium' ? 'Ca' : null)],
            );
        }

        foreach ([
            ['label' => 'Store in dry area', 'requirement' => 'Keep away from moisture.'],
            ['label' => 'Avoid direct sunlight', 'requirement' => 'Store under cover.'],
        ] as $row) {
            HandlingSpec::firstOrCreate(
                ['slug' => Str::slug($row['label'])],
                $row,
            );
        }

        foreach ([
            ['label' => 'Swine feed', 'description' => 'Complete feeds and premixes for swine.'],
            ['label' => 'Poultry feed', 'description' => 'Broiler and layer mineral programs.'],
            ['label' => 'Ruminant feed', 'description' => 'Dairy and beef mineral supplementation.'],
        ] as $row) {
            TypicalApplication::firstOrCreate(
                ['slug' => Str::slug($row['label'])],
                $row,
            );
        }
    }
}
