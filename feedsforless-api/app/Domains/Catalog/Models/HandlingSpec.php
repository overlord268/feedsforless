<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HandlingSpec extends Model
{
    protected $fillable = ['label', 'requirement'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'handling_spec_product');
    }
}