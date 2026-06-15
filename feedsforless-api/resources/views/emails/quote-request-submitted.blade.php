@extends('emails.partials.layout')

@section('title', 'Quote Request Received — FeedsForLess')
@section('email_tone', 'success')
@section('preheader', 'We received your quote request and our team is reviewing it.')
@section('badge', 'Request received')
@section('headline', 'Thank you for your request')
@section('subheadline')
    Hi{{ $contactName ? ' ' . $contactName : '' }}, we have your quote request on file.
@endsection

@section('content')
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 16px 0; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
        <tr>
            <td style="padding: 18px 20px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td class="stack" width="50%" style="padding-bottom: 8px; vertical-align: top;">
                            <p style="margin: 0 0 4px; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #94a3b8;">Request ID</p>
                            <p style="margin: 0; font-size: 18px; font-weight: 800; color: #0f172a; font-family: ui-monospace, monospace;">#{{ $quoteRequest->id }}</p>
                        </td>
                        <td class="stack" width="50%" style="vertical-align: top;">
                            <p style="margin: 0 0 4px; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #94a3b8;">Delivery ZIP</p>
                            <p style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;">{{ $quoteRequest->delivery_zip }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 20px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;">
        <tr>
            <td style="padding: 0;">
                <p style="margin: 0; padding: 14px 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b; background-color: #f1f5f9; border-radius: 10px 10px 0 0; border-bottom: 1px solid #e2e8f0;">
                    Requested items
                </p>
                @foreach ($quoteRequest->items as $index => $item)
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td style="padding: 14px 20px;{{ $index > 0 ? ' border-top: 1px solid #f1f5f9;' : '' }}">
                                <p style="margin: 0; font-size: 15px; font-weight: 600; color: #1e293b;">{{ $item->product?->name ?? 'Product' }}</p>
                                <p style="margin: 4px 0 0; font-size: 13px; color: #64748b;">{{ $item->qty }} units</p>
                            </td>
                        </tr>
                    </table>
                @endforeach
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 8px; color: #475569;">
        Our logistics team is reviewing routes and pricing. <strong style="color: #0f172a;">You will receive another email</strong> when your formal delivered quote is ready.
    </p>

    @if ($registerUrl)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 20px 0; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px;">
            <tr>
                <td style="padding: 18px 20px;">
                    <p style="margin: 0 0 6px; font-size: 14px; font-weight: 700; color: #1e40af;">Track this request faster</p>
                    <p style="margin: 0; font-size: 13px; color: #3b82f6; line-height: 1.5;">Create a free account to save addresses, view history, and manage future quotes.</p>
                </td>
            </tr>
        </table>
        @include('emails.partials.button', ['url' => $registerUrl, 'label' => 'Create free account', 'color' => '#2962ff'])
    @else
        @include('emails.partials.button', ['url' => $quotesUrl, 'label' => 'View my quotes', 'color' => '#2962ff'])
    @endif
@endsection
