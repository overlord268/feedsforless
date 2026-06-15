<?php

namespace App\Models;

use App\Domains\B2B\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FflSkuMapAudit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'map_type',
        'action',
        'before',
        'after',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
