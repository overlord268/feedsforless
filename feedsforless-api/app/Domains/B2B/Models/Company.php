<?php

namespace App\Domains\B2B\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'tax_classification',
        'tax_registration_number',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}