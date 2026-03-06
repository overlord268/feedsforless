<?php

namespace App\Domains\Quotes\Models;

use App\Domains\B2B\Models\User;
use App\Domains\B2B\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequest extends Model
{
    protected $fillable = [
        'request_by_id',
        'target_address_id',
        'delivery_zip',
        'requires_liftgate',
        'requires_appointment',
        'total_estimated_cost',
        'status',
        'admin_note',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'request_by_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'target_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }
}