<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
    protected $fillable = [
        'product_id',
        'test_method_id',
        'parameter_id',
        'specification',
        'measure_unit_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class); 
    }
    
    public function testMethod(): BelongsTo 
    { 
        return $this->belongsTo(TestMethod::class); 
    }
    
    public function parameter(): BelongsTo
    { 
        return $this->belongsTo(Parameter::class); 
    }
    
    public function measureUnit(): BelongsTo 
    { 
        return $this->belongsTo(MeasureUnit::class); 
    }
}