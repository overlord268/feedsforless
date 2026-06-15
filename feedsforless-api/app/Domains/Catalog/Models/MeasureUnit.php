<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Models\Concerns\HasCatalogSlug;
use Illuminate\Database\Eloquent\Model;

class MeasureUnit extends Model
{
    use HasCatalogSlug;

    protected $fillable = ['label', 'slug', 'notation'];
}