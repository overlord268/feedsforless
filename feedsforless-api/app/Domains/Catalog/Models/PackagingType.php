<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Models\Concerns\HasCatalogSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PackagingType extends Model
{
    use HasCatalogSlug;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_packaging')
            ->withPivot('id', 'quantity_per_pallet', 'base_price_per_unit')
            ->withTimestamps();
    }
}