<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NutritionalParameter extends Model
{
    protected $fillable = ['label', 'notation'];

    public function nutritionalAnalysisRows(): HasMany
    {
        return $this->hasMany(NutritionalAnalysis::class, 'nutritional_parameter_id');
    }
}
