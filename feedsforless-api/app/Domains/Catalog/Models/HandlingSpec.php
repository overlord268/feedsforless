<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Models\Concerns\HasCatalogSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HandlingSpec extends Model
{
    use HasCatalogSlug;

    protected $fillable = ['label', 'slug', 'requirement'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'handling_spec_product');
    }
}