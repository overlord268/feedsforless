<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolumePricingTier extends Model
{
    protected $fillable = [
        'product_packaging_id',
        'tier_name',
        'min_quantity',
        'max_quantity',
        'discount_percentage',
    ];

    public function productPackaging(): BelongsTo
    {
        return $this->belongsTo(ProductPackaging::class);
    }
}