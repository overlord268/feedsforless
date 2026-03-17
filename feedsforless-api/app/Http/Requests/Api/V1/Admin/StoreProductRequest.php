<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->productAttributesRules(), $this->nestedRelationsRules());
    }

    protected function productAttributesRules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'grade' => ['nullable', 'string', 'max:255'],
            'base_price_ref' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'stock_status' => ['nullable', 'string', 'in:in_stock,out_of_stock,backorder,call'],
            'availability' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
            'origin_address' => ['nullable', 'string'],
            'shelf_life_template' => ['nullable', 'string'],
            'lead_time' => ['nullable', 'date'],
            'max_lead_time' => ['nullable', 'date'],
            'tds_document_path' => ['nullable', 'string', 'max:500'],
            'sds_document_path' => ['nullable', 'string', 'max:500'],
            'coa_document_path' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function nestedRelationsRules(): array
    {
        return [
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'handling_spec_ids' => ['nullable', 'array'],
            'handling_spec_ids.*' => ['integer', 'exists:handling_specs,id'],
            'application_ids' => ['nullable', 'array'],
            'application_ids.*' => ['integer', 'exists:typical_applications,id'],
            'related_product_ids' => ['nullable', 'array'],
            'related_product_ids.*' => ['integer', 'exists:products,id'],
            'packaging' => ['nullable', 'array'],
            'packaging.*.packaging_type_id' => ['required', 'integer', 'exists:packaging_types,id'],
            'packaging.*.quantity_per_pallet' => ['required', 'integer', 'min:1'],
            'packaging.*.base_price_per_unit' => ['required', 'numeric', 'min:0'],
            'packaging.*.volume_tiers' => ['nullable', 'array'],
            'packaging.*.volume_tiers.*.tier_name' => ['required', 'string', 'max:255'],
            'packaging.*.volume_tiers.*.min_quantity' => ['required', 'integer', 'min:0'],
            'packaging.*.volume_tiers.*.max_quantity' => ['nullable', 'integer', 'min:0'],
            'packaging.*.volume_tiers.*.discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'nutritional_analysis' => ['nullable', 'array'],
            'nutritional_analysis.*.nutritional_parameter_id' => ['required', 'integer', 'exists:nutritional_parameters,id'],
            'nutritional_analysis.*.value' => ['nullable', 'string', 'max:255'],
            'nutritional_analysis.*.measure_unit_id' => ['nullable', 'integer', 'exists:measure_units,id'],
            'specifications' => ['nullable', 'array'],
            'specifications.*.parameter_id' => ['required', 'integer', 'exists:parameters,id'],
            'specifications.*.test_method_id' => ['required', 'integer', 'exists:test_methods,id'],
            'specifications.*.specification' => ['required', 'string', 'max:500'],
            'specifications.*.measure_unit_id' => ['required', 'integer', 'exists:measure_units,id'],
        ];
    }
}