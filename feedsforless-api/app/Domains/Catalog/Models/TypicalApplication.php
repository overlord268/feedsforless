<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Models\Concerns\HasCatalogSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TypicalApplication extends Model
{
    use HasCatalogSlug;

    protected $fillable = ['label', 'slug', 'description'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'application_product', 'application_id', 'product_id');
    }
}