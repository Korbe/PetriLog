@props(['url'])
<table cellpadding="0" cellspacing="0" role="presentation" class="pl-button" style="margin:28px 0 8px;">
    <tr>
        <td style="border-radius:999px; background-color:#118df0;">
            <a href="{{ $url }}" target="_blank" rel="noopener"
                style="display:inline-block; padding:14px 28px; border-radius:999px; background-color:#118df0; font-family:'Schibsted Grotesk', -apple-system, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:16px; font-weight:600; line-height:1.2; color:#ffffff; text-decoration:none; text-align:center;">
                {{ $slot }}&nbsp;&rarr;
            </a>
        </td>
    </tr>
</table>
