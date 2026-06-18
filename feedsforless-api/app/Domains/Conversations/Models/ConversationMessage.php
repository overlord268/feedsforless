<?php

namespace App\Domains\Conversations\Models;

use App\Domains\B2B\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMessage extends Model
{
    public const SENDER_GUEST = 'guest';

    public const SENDER_CUSTOMER = 'customer';

    public const SENDER_ADMIN = 'admin';

    public const SENDER_SYSTEM = 'system';

    public const TYPE_TEXT = 'text';

    public const TYPE_QUOTE_REFERENCE = 'quote_reference';

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_user_id',
        'message_type',
        'body',
        'metadata',
        'read_by_admin_at',
        'read_by_customer_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'read_by_admin_at' => 'datetime',
            'read_by_customer_at' => 'datetime',
        ];
    }

    public function isQuoteReference(): bool
    {
        return $this->message_type === self::TYPE_QUOTE_REFERENCE;
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function isFromStaff(): bool
    {
        return $this->sender_type === self::SENDER_ADMIN;
    }

    public function isFromCustomerSide(): bool
    {
        return in_array($this->sender_type, [self::SENDER_GUEST, self::SENDER_CUSTOMER], true);
    }
}
