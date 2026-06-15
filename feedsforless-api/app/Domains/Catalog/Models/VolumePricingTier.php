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
        'pricing_mode',
        'discount_percentage',
        'fixed_price',
        'profit_margin_percent',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'float',
            'fixed_price' => 'float',
            'profit_margin_percent' => 'float',
        ];
    }

    public function productPackaging(): BelongsTo
    {
        return $this->belongsTo(ProductPackaging::class);
    }
}