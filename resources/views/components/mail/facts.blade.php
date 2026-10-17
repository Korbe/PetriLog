{{-- Schlüssel/Wert-Tabelle; Werte werden escaped, HtmlString-Werte (z.B. Links) bleiben HTML --}}
@props(['rows' => []])
<table width="100%" cellpadding="0" cellspacing="0" role="presentation"
    style="margin:4px 0 24px; border:1px solid #ece7dc; border-radius:12px; border-collapse:separate; background-color:#f9f7f2;">
    @foreach ($rows as $label => $value)
        <tr>
            <td style="padding:11px 16px; {{ $loop->last ? '' : 'border-bottom:1px solid #ece7dc;' }} width:34%; vertical-align:top; font-family:'JetBrains Mono', Menlo, Consolas, monospace; font-size:11px; line-height:1.9; letter-spacing:0.1em; text-transform:uppercase; color:#5b6669;">
                {{ $label }}
            </td>
            <td style="padding:11px 16px; {{ $loop->last ? '' : 'border-bottom:1px solid #ece7dc;' }} vertical-align:top; font-size:15px; line-height:1.5; color:#0f1d22; word-break:break-word;">
                {{ filled((string) $value) ? $value : '—' }}
            </td>
        </tr>
    @endforeach
</table>
