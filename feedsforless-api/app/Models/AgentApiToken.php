<?php

namespace App\Models;

use App\Domains\B2B\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AgentApiToken extends Model
{
    protected $fillable = [
        'name',
        'token_prefix',
        'token_hash',
        'created_by_user_id',
        'last_used_at',
        'revoked_at',
        'rotated_from_id',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function rotatedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'rotated_from_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    public function matches(string $plainToken): bool
    {
        return hash_equals($this->token_hash, hash('sha256', $plainToken));
    }

    public function touchLastUsed(): void
    {
        $this->forceFill(['last_used_at' => now()])->save();
    }

    public static function hashPlainToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public static function extractPrefix(string $plainToken): string
    {
        return substr($plainToken, 0, 16);
    }
}
