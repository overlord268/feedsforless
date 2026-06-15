@extends('emails.partials.layout')

@section('title', 'New chat message')
@section('email_tone'){{ $isToCustomer ? 'ready' : 'brand' }}@endsection
@section('preheader', \Illuminate\Support\Str::limit($chatMessage->body, 120))
@section('badge', $isToCustomer ? 'New reply' : 'Customer message')
@section('headline', $isToCustomer ? 'You have a new message' : 'New customer chat message')
@section('subheadline')
    Hi{{ $recipientName ? ' ' . $recipientName : '' }}, a message was posted in conversation #{{ $conversation->id }}.
@endsection

@section('content')
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 16px 0; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
        <tr>
            <td style="padding: 18px 20px;">
                <p style="margin: 0 0 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #94a3b8;">Message</p>
                <p style="margin: 0; font-size: 15px; color: #1e293b; line-height: 1.6; white-space: pre-wrap;">{{ $chatMessage->body }}</p>
            </td>
        </tr>
    </table>

    @php
        $chatUrl = $actionUrl ?? rtrim(config('app.frontend_url', config('app.url')), '/')
            . ($isToCustomer ? '/messages' : '/admin/messages?conversation=' . $conversation->id);
    @endphp

    @include('emails.partials.button', [
        'url' => $chatUrl,
        'label' => $isToCustomer ? 'Open conversation' : 'Reply in admin',
        'color' => '#2962ff',
    ])

    <p style="margin: 16px 0 0; font-size: 13px; color: #94a3b8;">
        You can also reply from this email thread if your mail client supports it, or use the link above for the full history.
    </p>
@endsection
