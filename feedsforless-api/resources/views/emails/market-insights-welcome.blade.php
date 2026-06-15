@extends('emails.partials.layout')

@section('title', 'Market Insights — subscription confirmed')
@section('email_tone', 'success')
@section('preheader', 'Your FeedsForLess Market Insights subscription is active.')
@section('badge', 'Confirmed')
@section('headline', "You're on the list")
@section('subheadline')
    @if($subscription->name)
        Hello {{ $subscription->name }}, welcome to Market Insights.
    @else
        Welcome to FeedsForLess Market Insights.
    @endif
@endsection

@section('content')
    {{-- Confirmation summary --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 24px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
        <tr>
            <td style="padding: 20px 24px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td width="44" valign="top" style="padding-right: 14px;">
                            <div style="width: 40px; height: 40px; border-radius: 999px; background-color: #059669; color: #ffffff; font-size: 20px; font-weight: 700; line-height: 40px; text-align: center;">✓</div>
                        </td>
                        <td valign="top">
                            <p style="margin: 0 0 6px; font-size: 15px; font-weight: 700; color: #0f172a;">Subscription active</p>
                            <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.55;">
                                We will send market intelligence, price alerts, and procurement updates to
                                <strong style="color: #0f172a;">{{ $subscription->email }}</strong>.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- What happens next --}}
    <p style="margin: 0 0 10px; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #64748b;">What you will receive</p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 28px; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
        <tr>
            <td style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; line-height: 1.5;">
                <strong style="color: #0f172a;">Weekly reports</strong> — supply, demand, and commodity movement
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; line-height: 1.5;">
                <strong style="color: #0f172a;">Early access</strong> — priority notice on inventory and offers
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 20px; font-size: 14px; color: #334155; line-height: 1.5;">
                <strong style="color: #0f172a;">Price alerts</strong> — grains, seeds, and fertilizers
            </td>
        </tr>
    </table>
    
    {{-- Account CTA --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 8px; background-color: #003366; border-radius: 12px;">
        <tr>
            <td style="padding: 24px 28px; text-align: center;">
                <p style="margin: 0 0 8px; font-size: 16px; font-weight: 700; color: #ffffff;">Get more from FeedsForLess</p>
                <p style="margin: 0 0 20px; font-size: 14px; color: #cbd5e1; line-height: 1.55;">
                    Create a free account to request delivered quotes, save RFQs, and chat with our team.
                </p>
                @include('emails.partials.button', [
                    'url' => $registerUrl,
                    'label' => 'Create free account',
                    'color' => '#2563eb',
                ])
            </td>
        </tr>
    </table>


    @if ($suggestedProductName && $suggestedProductUrl)
        <p style="margin: 0 0 10px; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #64748b;">Suggested for your operation</p>
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 28px; background-color: #ffffff; border: 1px solid #cbd5e1; border-left: 4px solid #2563eb; border-radius: 12px;">
            <tr>
                <td style="padding: 22px 24px;">
                    <p style="margin: 0 0 4px; font-size: 12px; font-weight: 600; color: #2563eb; text-transform: uppercase; letter-spacing: 0.06em;">Commodity spotlight</p>
                    <p style="margin: 0 0 12px; font-size: 18px; font-weight: 800; color: #0f172a; line-height: 1.35;">{{ $suggestedProductName }}</p>
                    <p style="margin: 0 0 18px; font-size: 14px; color: #475569; line-height: 1.6;">
                        While you wait for your first report, explore this product in our catalog and see if it fits your procurement needs.
                    </p>
                    @include('emails.partials.button', [
                        'url' => $suggestedProductUrl,
                        'label' => 'View in catalog',
                        'color' => '#2563eb',
                    ])
                </td>
            </tr>
        </table>
    @endif

    <p style="margin: 20px 0 0; font-size: 13px; color: #64748b; text-align: center; line-height: 1.5;">
        Prefer to browse first?
        <a href="{{ $catalogUrl }}" style="color: #2563eb; font-weight: 600; text-decoration: none;">Open the catalog</a>
    </p>
@endsection
