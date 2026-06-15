@php
    $statusLabels = [
        'rejected' => 'Not approved',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
    ];
    $statusLabel = $statusLabels[$quoteRequest->status] ?? ucfirst($quoteRequest->status);
@endphp
@extends('emails.partials.layout')

@section('title', 'Quote Request Update — FeedsForLess')
@section('email_tone', 'alert')
@section('preheader', 'There is an update on your quote request #' . $quoteRequest->id . '.')
@section('badge', $statusLabel)
@section('headline', 'Update on your quote request')
@section('subheadline')
    Hi{{ $contactName ? ' ' . $contactName : '' }}, here is the latest status for request #{{ $quoteRequest->id }}.
@endsection

@section('content')
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 16px 0;">
        <tr>
            <td style="padding: 16px 20px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td width="40" style="vertical-align: top; padding-right: 14px;">
                            <div style="width: 36px; height: 36px; background-color: #fee2e2; border-radius: 8px; text-align: center; line-height: 36px; font-size: 18px;">!</div>
                        </td>
                        <td style="vertical-align: top;">
                            <p style="margin: 0 0 4px; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #94a3b8;">Status</p>
                            <p style="margin: 0; font-size: 17px; font-weight: 700; color: #b91c1c;">{{ $statusLabel }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if ($reason)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 20px; border-left: 4px solid #f87171; background-color: #fffbeb; border-radius: 0 10px 10px 0;">
            <tr>
                <td style="padding: 18px 22px;">
                    <p style="margin: 0 0 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #b45309;">Message from our team</p>
                    <p style="margin: 0; font-size: 15px; color: #78350f; line-height: 1.6; white-space: pre-wrap;">{{ $reason }}</p>
                </td>
            </tr>
        </table>
    @else
        <p style="margin: 0 0 20px; color: #64748b; font-size: 14px;">
            If you have questions about this decision or would like to discuss alternatives, reply to this email or contact our team.
        </p>
    @endif

    <p style="margin: 0 0 8px; color: #475569;">
        You can submit a new quote request anytime from our catalog with updated volumes or delivery details.
    </p>

    @if ($quoteRequest->request_by_id)
        @include('emails.partials.button', ['url' => $quotesUrl, 'label' => 'View my quotes', 'color' => '#475569'])
    @endif
@endsection
