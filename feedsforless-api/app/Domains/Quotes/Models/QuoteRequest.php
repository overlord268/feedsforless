<?php

namespace App\Domains\Quotes\Models;

use App\Domains\B2B\Models\User;
use App\Domains\B2B\Models\Address;
use App\Domains\Conversations\Models\Conversation;
use App\Domains\Conversations\Models\ConversationMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuoteRequest extends Model
{
    protected $fillable = [
        'request_by_id',
        'guest_email',
        'guest_company_name',
        'guest_contact_name',
        'guest_first_name',
        'guest_last_name',
        'guest_phone',
        'guest_destination_address',
        'guest_tax_id',
        'target_address_id',
        'delivery_zip',
        'requires_liftgate',
        'requires_appointment',
        'total_estimated_cost',
        'status',
        'admin_note',
        'customer_message',
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

    public function quoteConversation(): HasOne
    {
        return $this->hasOne(Conversation::class, 'quote_request_id');
    }

    public function unreadQuoteChatMessages(): HasManyThrough
    {
        return $this->hasManyThrough(
            ConversationMessage::class,
            Conversation::class,
            'quote_request_id',
            'conversation_id',
            'id',
            'id'
        )->whereIn('conversation_messages.sender_type', [
            ConversationMessage::SENDER_GUEST,
            ConversationMessage::SENDER_CUSTOMER,
        ])->whereNull('conversation_messages.read_by_admin_at');
    }
}