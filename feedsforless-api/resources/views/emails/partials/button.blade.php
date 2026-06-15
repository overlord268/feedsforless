@php
    $url = $url ?? '#';
    $label = $label ?? 'Continue';
    $color = $color ?? '#2962ff';
@endphp
<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: 20px auto 8px;">
    <tr>
        <td align="center" style="border-radius: 8px; background-color: {{ $color }};">
            <a href="{{ $url }}" target="_blank" style="display: inline-block; padding: 13px 28px; font-size: 13px; font-weight: 700; letter-spacing: 0.03em; color: #ffffff !important; text-decoration: none; border-radius: 8px; line-height: 1.2; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
