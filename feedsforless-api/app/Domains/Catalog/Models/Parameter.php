<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $fillable = ['label', 'type'];
}