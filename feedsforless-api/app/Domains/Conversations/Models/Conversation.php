<?php

namespace App\Domains\Conversations\Models;

use App\Domains\B2B\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'guest_email',
        'guest_name',
        'guest_access_token',
        'status',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ConversationMessage::class)->latestOfMany();
    }

    public function customerDisplayName(): string
    {
        if ($this->user) {
            $name = trim(($this->user->first_name ?? '') . ' ' . ($this->user->last_name ?? ''));

            return $name !== '' ? $name : $this->user->email;
        }

        return $this->guest_name ?: $this->guest_email ?: 'Guest';
    }

    public function customerEmail(): ?string
    {
        return $this->user?->email ?? $this->guest_email;
    }
}
