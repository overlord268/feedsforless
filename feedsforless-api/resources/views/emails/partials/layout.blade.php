@php
    $tone = trim($__env->yieldContent('email_tone')) ?: 'brand';
    $accent = match ($tone) {
        'success' => ['bar' => '#059669', 'bg' => '#ecfdf5', 'text' => '#047857', 'btn' => '#059669'],
        'ready' => ['bar' => '#2563eb', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'btn' => '#2962ff'],
        'alert' => ['bar' => '#dc2626', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'btn' => '#475569'],
        default => ['bar' => '#003366', 'bg' => '#f0f4ff', 'text' => '#003366', 'btn' => '#2962ff'],
    };
    $headline = trim($__env->yieldContent('headline'));
    $preheader = trim($__env->yieldContent('preheader'));
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'FeedsForLess')</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        body, table, td, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #e2e8f0; }
        @media only screen and (max-width: 620px) {
            .wrapper { width: 100% !important; }
            .content-pad { padding-left: 20px !important; padding-right: 20px !important; }
            .stack { display: block !important; width: 100% !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    @if ($preheader !== '')
        <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">{{ $preheader }}&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;</div>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #e2e8f0;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" class="wrapper" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%;">

                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #003366 0%, #004080 50%, #003366 100%); background-color: #003366; border-radius: 12px 12px 0 0; padding: 28px 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <p style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.02em; color: #ffffff; line-height: 1.2;">
                                            Feeds<span style="color: #60a5fa;">For</span>Less
                                        </p>
                                        <p style="margin: 6px 0 0; font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: #94a3b8;">
                                            Industrial Feed Commodity Portal
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Accent bar + headline --}}
                    <tr>
                        <td style="background-color: #ffffff; border-left: 4px solid {{ $accent['bar'] }};">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td class="content-pad" style="padding: 28px 32px 8px;">
                                        @hasSection('badge')
                                            <p style="margin: 0 0 12px;">
                                                <span style="display: inline-block; padding: 6px 12px; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: {{ $accent['text'] }}; background-color: {{ $accent['bg'] }}; border-radius: 6px;">
                                                    @yield('badge')
                                                </span>
                                            </p>
                                        @endif
                                        @if ($headline !== '')
                                            <h1 style="margin: 0; font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; line-height: 1.25; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                                                {!! $headline !!}
                                            </h1>
                                        @endif
                                        @hasSection('subheadline')
                                            <p style="margin: 10px 0 0; font-size: 15px; color: #64748b; line-height: 1.5;">
                                                @yield('subheadline')
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background-color: #ffffff;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td class="content-pad" style="padding: 8px 32px 32px; font-size: 15px; line-height: 1.65; color: #334155;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f8fafc; border-radius: 0 0 12px 12px; border-top: 1px solid #e2e8f0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td class="content-pad" style="padding: 24px 32px; text-align: center;">
                                        <p style="margin: 0 0 8px; font-size: 13px; font-weight: 600; color: #475569;">
                                            FeedsForLess
                                        </p>
                                        <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">
                                            B2B feed commodities · Logistics &amp; delivered pricing
                                        </p>
                                        <p style="margin: 16px 0 0; font-size: 11px; color: #cbd5e1;">
                                            This is an automated message. Please do not reply directly unless you need assistance.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
