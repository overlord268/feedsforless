<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionalAnalysis extends Model
{
    protected $table = 'nutritional_analysis';

    protected $fillable = [
        'product_id',
        'nutritional_parameter_id',
        'value',
        'measure_unit_id'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function nutritionalParameter(): BelongsTo
    {
        return $this->belongsTo(NutritionalParameter::class);
    }

    public function measureUnit(): BelongsTo
    {
        return $this->belongsTo(MeasureUnit::class);
    }
}