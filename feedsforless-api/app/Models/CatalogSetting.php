<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];
}
