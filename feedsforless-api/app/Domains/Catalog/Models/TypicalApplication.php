<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TypicalApplication extends Model
{
    protected $fillable = ['label', 'description'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'application_product', 'application_id', 'product_id');
    }
}