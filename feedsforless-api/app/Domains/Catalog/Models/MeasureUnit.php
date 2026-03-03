<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

class MeasureUnit extends Model
{
    protected $fillable = ['label', 'notation'];
}