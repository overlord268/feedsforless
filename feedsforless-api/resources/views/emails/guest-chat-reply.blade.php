@extends('emails.partials.layout')

@section('title', 'Reply to your message')
@section('email_tone', 'ready')
@section('preheader', \Illuminate\Support\Str::limit($chatMessage->body, 140))
@section('badge', 'Team reply')
@section('headline', 'Our team replied to your message')
@section('subheadline')
    Hi{{ $recipientName ? ' ' . $recipientName : '' }}, you contacted us without a registered account. Here is our response.
@endsection

@section('content')
    <p style="margin: 0 0 16px; color: #475569; font-size: 14px; line-height: 1.6;">
        You wrote to us from the website chat as a <strong>guest</strong> (no FeedsForLess login required).
        Because you are not registered yet, we are sending this reply to <strong>{{ $conversation->guest_email }}</strong>.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 20px; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px;">
        <tr>
            <td style="padding: 20px 22px;">
                <p style="margin: 0 0 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #2563eb;">FeedsForLess team</p>
                <p style="margin: 0; font-size: 16px; color: #0f172a; line-height: 1.65; white-space: pre-wrap;">{{ $chatMessage->body }}</p>
            </td>
        </tr>
    </table>

    @php
        $chatUrl = $actionUrl ?? rtrim(config('app.frontend_url', config('app.url')), '/') . '/?openChat=1';
    @endphp

    <p style="margin: 0 0 12px; color: #475569; font-size: 14px;">
        Click below to open the chat and continue where you left off — no login required.
    </p>

    @include('emails.partials.button', [
        'url' => $chatUrl,
        'label' => 'Continue conversation',
        'color' => '#2962ff',
    ])

    <p style="margin: 16px 0 0; font-size: 13px; color: #94a3b8; line-height: 1.5;">
        Want to track quotes and orders in one place?
        <a href="{{ rtrim(config('app.frontend_url', config('app.url')), '/') }}/register" style="color: #2563eb; font-weight: 600;">Create a free account</a>
        (optional).
    </p>
@endsection
