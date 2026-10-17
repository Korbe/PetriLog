{{-- Abgesetzter Block, z.B. für Ticket-Beschreibungen --}}
@props(['title' => null])
@if ($title)
    <p style="margin:24px 0 8px; font-family:'JetBrains Mono', Menlo, Consolas, monospace; font-size:11px; letter-spacing:0.14em; text-transform:uppercase; color:#5b6669;">
        {{ $title }}
    </p>
@endif
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 8px;">
    <tr>
        <td style="border-left:3px solid #118df0; background-color:#f4f9fe; border-radius:0 10px 10px 0; padding:16px 18px 2px; font-size:15px; line-height:1.6; color:#0f1d22;">
            {{ $slot }}
        </td>
    </tr>
</table>
