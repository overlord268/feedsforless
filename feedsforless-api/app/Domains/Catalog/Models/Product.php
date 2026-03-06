<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'grade',
        'base_price_ref',
        'description',
        'origin_address',
        'stock_status',
        'availability',
        'lead_time',
        'max_lead_time',
        'shelf_life_template',
        'status',
        'reject_reason',
        'tds_document_path',
        'sds_document_path',
        'coa_document_path',
        'market_trends_link',
    ];

    protected $casts = [
        'lead_time' => 'date',
        'max_lead_time' => 'date',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function packaging(): HasMany
    {
        return $this->hasMany(ProductPackaging::class);
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(Specification::class);
    }
    public function handlingSpecs(): BelongsToMany
    {
        return $this->belongsToMany(HandlingSpec::class, 'handling_spec_product');
    }
    public function typicalApplications(): BelongsToMany
    {
        return $this->belongsToMany(TypicalApplication::class, 'application_product', 'product_id', 'application_id');
    }
    public function nutritionalAnalysis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NutritionalAnalysis::class);
    }
    public function relatedProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RelatedProduct::class, 'node_id');
    }
}