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
use Symfony\Component\Yaml\Yaml;

class CatalogMastersYamlExporter
{
    public function export(bool $includeProducts = false): string
    {
        $slugById = Category::pluck('slug', 'id');

        $categories = Category::orderBy('label')->get()->map(fn (Category $c) => [
            'slug' => $c->slug,
            'label' => $c->label,
            'parent_slug' => $c->parent_id ? ($slugById[$c->parent_id] ?? null) : null,
        ])->values()->all();

        $data = [
            'meta' => [
                'version' => '1',
                'generated_at' => now()->toIso8601String(),
            ],
            'constraints' => $this->constraints(),
            'categories' => $categories,
            'packaging_types' => PackagingType::orderBy('name')->get(['slug', 'name'])->toArray(),
            'parameters' => Parameter::orderBy('label')->get(['slug', 'label', 'type'])->toArray(),
            'test_methods' => TestMethod::orderBy('label')->get(['slug', 'label'])->toArray(),
            'measure_units' => MeasureUnit::orderBy('label')->get(['slug', 'label', 'notation'])->toArray(),
            'nutritional_parameters' => NutritionalParameter::orderBy('label')->get(['slug', 'label', 'notation'])->toArray(),
            'handling_specs' => HandlingSpec::orderBy('label')->get(['slug', 'label', 'requirement'])->toArray(),
            'typical_applications' => TypicalApplication::orderBy('label')->get(['slug', 'label', 'description'])->toArray(),
        ];

        if ($includeProducts) {
            $data['products'] = Product::orderBy('name')
                ->get(['slug', 'sku', 'name'])
                ->toArray();
        }

        return Yaml::dump($data, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }

    /**
     * @return array<string, mixed>
     */
    private function constraints(): array
    {
        return [
            'slug_pattern' => '^[a-z0-9]+(?:-[a-z0-9]+)*$',
            'product' => [
                'required' => ['slug', 'name', 'status', 'category_slugs'],
                'status' => ['draft', 'published', 'archived'],
                'stock_status' => ['in_stock', 'out_of_stock', 'backorder', 'call'],
                'defaults' => [
                    'status' => 'draft',
                    'stock_status' => 'in_stock',
                ],
            ],
            'packaging' => [
                'required_per_item' => ['presentation_index', 'packaging_type_slug', 'quantity_per_pallet', 'base_price_per_unit'],
                'volume_tiers' => [
                    'fields_per_item' => 7,
                    'format' => 'presentation_index|tier_name|min_quantity|max_quantity|pricing_mode|discount_percentage|fixed_price',
                    'pricing_mode' => ['percentage', 'fixed_price', '%', '$'],
                    'legacy_fields_per_item' => 5,
                    'legacy_format' => 'presentation_index|tier_name|min_quantity|max_quantity|discount_percentage',
                    'notes' => [
                        'All tiers in one presentation must share the same pricing_mode.',
                        'Use percentage for All % tiers (discount_percentage); fixed_price for All $ tiers (fixed_price).',
                        'Empty max_quantity on the last tier means unlimited (∞).',
                    ],
                ],
            ],
            'nutritional_analysis' => [
                'required_per_item' => ['parameter_slug'],
                'optional' => ['value', 'measure_unit_slug'],
            ],
            'specifications' => [
                'required_per_item' => ['parameter_slug', 'test_method_slug', 'specification', 'measure_unit_slug'],
            ],
        ];
    }
}
