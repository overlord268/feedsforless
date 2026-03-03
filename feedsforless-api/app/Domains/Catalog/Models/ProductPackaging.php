<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPackaging extends Model
{
    protected $table = 'product_packaging';

    protected $fillable = [
        'product_id',
        'packaging_type_id',
        'quantity_per_pallet',
        'base_price_per_unit',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function packagingType(): BelongsTo
    {
        return $this->belongsTo(PackagingType::class);
    }
    public function tiers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VolumePricingTier::class);
    }
}