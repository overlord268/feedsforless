@extends('emails.partials.layout')

@section('title', 'Your Quote Is Ready — FeedsForLess')
@section('email_tone', 'ready')
@section('preheader', 'Your formal quote is ready to review.')
@section('badge', 'Quote ready')
@section('headline', 'Your delivered quote is ready')
@section('subheadline')
    Hi{{ $contactName ? ' ' . $contactName : '' }}, request #{{ $quoteRequest->id }} has been priced.
@endsection

@section('content')
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 16px 0; background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%); background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px;">
        <tr>
            <td style="padding: 28px 24px; text-align: center;">
                <p style="margin: 0 0 6px; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #3b82f6;">Estimated total</p>
                <p style="margin: 0; font-size: 36px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; line-height: 1.1;">
                    ${{ number_format((float) ($quoteRequest->total_estimated_cost ?? 0), 2) }}
                </p>
                <p style="margin: 12px 0 0; font-size: 13px; color: #64748b;">
                    Delivery ZIP <strong style="color: #334155;">{{ $quoteRequest->delivery_zip }}</strong>
                </p>
            </td>
        </tr>
    </table>

    @if ($quoteRequest->items && $quoteRequest->items->count())
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 20px; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
            <tr>
                <td style="padding: 12px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <p style="margin: 0; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b;">Line items</p>
                </td>
            </tr>
            @foreach ($quoteRequest->items as $item)
                <tr>
                    <td style="padding: 12px 20px; border-bottom: 1px solid #f1f5f9;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="font-size: 14px; font-weight: 600; color: #1e293b;">{{ $item->product?->name ?? 'Item' }}</td>
                                <td align="right" style="font-size: 13px; color: #64748b; white-space: nowrap;">{{ $item->qty }} units</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    <p style="margin: 0 0 8px; color: #475569;">
        Sign in to review full details, <strong style="color: #0f172a;">accept the quote</strong>, or decline if it does not meet your needs.
    </p>

    @include('emails.partials.button', ['url' => $quotesUrl, 'label' => 'Review & respond', 'color' => '#2962ff'])

    <p style="margin: 16px 0 0; font-size: 13px; color: #94a3b8; text-align: center;">
        Prices reflect current freight indexes and may be subject to terminal surcharges at booking.
    </p>
@endsection
