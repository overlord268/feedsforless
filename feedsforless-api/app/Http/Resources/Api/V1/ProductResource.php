<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'grade' => $this->grade,
            'base_price_ref' => $this->base_price_ref !== null ? (float) $this->base_price_ref : null,
            'description' => $this->description,
            'lead_time' => $this->lead_time?->format('Y-m-d'),
            'max_lead_time' => $this->max_lead_time?->format('Y-m-d'),
            'stock_status' => $this->stock_status,
            'availability' => $this->availability,
            'status' => $this->status,
            'categories' => $this->whenLoaded('categories', fn() => $this->categories->map(fn($c) => [
                'id' => $c->id,
                'label' => $c->label,
                'slug' => $c->slug,
            ])),
            'origin_address' => $this->origin_address,
            'shelf_life_template' => $this->shelf_life_template,
            'packaging_options' => $this->whenLoaded('packaging', fn () => $this->packaging->map(function ($pack) {
                return [
                    'id' => $pack->id,
                    'packaging_type_id' => $pack->packaging_type_id,
                    'type_name' => $pack->packagingType?->name,
                    'quantity_per_pallet' => $pack->quantity_per_pallet,
                    'base_price_per_unit' => $pack->base_price_per_unit,
                    'volume_tiers' => $pack->relationLoaded('tiers') ? $pack->tiers->map(fn ($t) => [
                        'id' => $t->id,
                        'tier_name' => $t->tier_name,
                        'min_quantity' => $t->min_quantity,
                        'max_quantity' => $t->max_quantity,
                        'discount_percentage' => $t->discount_percentage,
                    ]) : [],
                ];
            })),
            'handling_specs' => $this->whenLoaded('handlingSpecs', fn () => $this->handlingSpecs->map(fn ($h) => ['id' => $h->id, 'label' => $h->label])),
            'typical_applications' => $this->whenLoaded('typicalApplications', fn () => $this->typicalApplications->map(fn ($a) => ['id' => $a->id, 'label' => $a->label])),
            'nutritional_analysis' => $this->whenLoaded('nutritionalAnalysis', fn () => $this->nutritionalAnalysis->map(fn ($n) => [
                'nutritional_parameter_id' => $n->nutritional_parameter_id,
                'value' => $n->value,
                'measure_unit_id' => $n->measure_unit_id,
            ])),
            'specifications' => $this->whenLoaded('specifications', fn () => $this->specifications->map(fn ($s) => [
                'parameter_id' => $s->parameter_id,
                'test_method_id' => $s->test_method_id,
                'specification' => $s->specification,
                'measure_unit_id' => $s->measure_unit_id,
            ])),
            'related_product_ids' => $this->whenLoaded('relatedProducts', fn () => $this->relatedProducts->pluck('link_id')->values()->all()),
            'tds_document_path' => $this->tds_document_path,
            'sds_document_path' => $this->sds_document_path,
            'coa_document_path' => $this->coa_document_path,
        ];
    }
}