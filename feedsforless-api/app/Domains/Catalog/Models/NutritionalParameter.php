<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Models\Concerns\HasCatalogSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NutritionalParameter extends Model
{
    use HasCatalogSlug;

    protected $fillable = ['label', 'slug', 'notation'];

    public function nutritionalAnalysisRows(): HasMany
    {
        return $this->hasMany(NutritionalAnalysis::class, 'nutritional_parameter_id');
    }
}
