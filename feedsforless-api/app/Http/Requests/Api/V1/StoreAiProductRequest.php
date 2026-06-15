<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $slugRule = ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];

        return [
            'slug' => $slugRule,
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'stock_status' => ['nullable', 'string', Rule::in(['in_stock', 'out_of_stock', 'backorder', 'call'])],
            'grade' => ['nullable', 'string', 'max:255'],
            'base_price_ref' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'availability' => ['nullable', 'string', 'max:255'],
            'origin_address' => ['nullable', 'string'],
            'shelf_life_template' => ['nullable', 'string'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'max_lead_time_days' => ['nullable', 'integer', 'min:0'],
            'market_trends_link' => ['nullable', 'string', 'max:500'],
            'tds_document_url' => ['nullable', 'string', 'max:500'],
            'sds_document_url' => ['nullable', 'string', 'max:500'],
            'coa_document_url' => ['nullable', 'string', 'max:500'],
            'category_slugs' => ['required', 'array', 'min:1'],
            'category_slugs.*' => ['string', 'max:255'],
            'handling_spec_slugs' => ['nullable', 'array'],
            'handling_spec_slugs.*' => ['string', 'max:255'],
            'application_slugs' => ['nullable', 'array'],
            'application_slugs.*' => ['string', 'max:255'],
            'related_product_slugs' => ['nullable', 'array'],
            'related_product_slugs.*' => ['string', 'max:255'],
            'packaging' => ['nullable', 'array'],
            'packaging.*.presentation_index' => ['required', 'integer', 'min:1'],
            'packaging.*.packaging_type_slug' => ['required', 'string', 'max:255'],
            'packaging.*.quantity_per_pallet' => ['required', 'integer', 'min:1'],
            'packaging.*.base_price_per_unit' => ['required', 'numeric', 'min:0'],
            'packaging.*.volume_tiers' => ['nullable', 'array'],
            'packaging.*.volume_tiers.*.tier_name' => ['required', 'string', 'max:255'],
            'packaging.*.volume_tiers.*.min_quantity' => ['required', 'integer', 'min:0'],
            'packaging.*.volume_tiers.*.max_quantity' => ['nullable', 'integer', 'min:0'],
            'packaging.*.volume_tiers.*.pricing_mode' => ['nullable', 'string', 'in:percentage,fixed_price'],
            'packaging.*.volume_tiers.*.discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'packaging.*.volume_tiers.*.fixed_price' => ['nullable', 'numeric', 'min:0'],
            'nutritional_analysis' => ['nullable', 'array'],
            'nutritional_analysis.*.parameter_slug' => ['required', 'string', 'max:255'],
            'nutritional_analysis.*.value' => ['nullable', 'string', 'max:255'],
            'nutritional_analysis.*.measure_unit_slug' => ['nullable', 'string', 'max:255'],
            'specifications' => ['nullable', 'array'],
            'specifications.*.parameter_slug' => ['required', 'string', 'max:255'],
            'specifications.*.test_method_slug' => ['required', 'string', 'max:255'],
            'specifications.*.specification' => ['required', 'string', 'max:500'],
            'specifications.*.measure_unit_slug' => ['required', 'string', 'max:255'],
        ];
    }
}
