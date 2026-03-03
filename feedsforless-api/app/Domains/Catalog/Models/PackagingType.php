<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PackagingType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_packaging')
            ->withPivot('id', 'quantity_per_pallet', 'base_price_per_unit')
            ->withTimestamps();
    }
}