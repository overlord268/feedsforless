<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FflSkuGrade extends Model
{
    protected $fillable = [
        'grade_spec',
        'sku_code',
    ];
}
