{{-- Nummerierte Schritte wie im "So geht's"-Abschnitt der Website: [['title' => ..., 'text' => ...], ...] --}}
@props(['items' => []])
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:8px 0 8px;">
    @foreach ($items as $item)
        <tr>
            <td width="44" style="padding:12px 0; {{ $loop->last ? '' : 'border-bottom:1px solid #ece7dc;' }} vertical-align:top; font-family:'JetBrains Mono', Menlo, Consolas, monospace; font-size:13px; line-height:1.6; color:#118df0;">
                {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
            </td>
            <td style="padding:12px 0; {{ $loop->last ? '' : 'border-bottom:1px solid #ece7dc;' }} vertical-align:top;">
                <p style="margin:0 0 2px; font-family:'Manrope', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:16px; line-height:1.4; font-weight:700; letter-spacing:-0.01em; color:#0f1d22;">
                    {{ $item['title'] }}
                </p>
                <p style="margin:0; font-size:15px; line-height:1.55; color:#5b6669;">
                    {{ $item['text'] }}
                </p>
            </td>
        </tr>
    @endforeach
</table>
