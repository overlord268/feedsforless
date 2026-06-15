<?php

namespace App\Mail;

use App\Domains\Conversations\Models\Conversation;
use App\Domains\Conversations\Models\ConversationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChatMessageNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public ConversationMessage $chatMessage,
        public string $recipientName,
        public bool $isToCustomer,
        public bool $isGuestWithoutAccount = false,
        public ?string $actionUrl = null,
        public ?string $customerLabel = null,
    ) {}

    public function envelope(): Envelope
    {
        $from = $this->customerLabel ?? $this->conversation->customerDisplayName();

        if ($this->isToCustomer && $this->isGuestWithoutAccount) {
            return new Envelope(
                subject: 'FeedsForLess – Reply to your message',
                replyTo: [config('mail.from.address')],
            );
        }

        return new Envelope(
            subject: $this->isToCustomer
                ? 'FeedsForLess – New message from our team'
                : "FeedsForLess – New chat message from {$from}",
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->isToCustomer && $this->isGuestWithoutAccount
                ? 'emails.guest-chat-reply'
                : 'emails.chat-message-notification',
        );
    }
}
