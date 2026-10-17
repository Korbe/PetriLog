@props(['eyebrow' => null])
@if ($eyebrow)
    <p style="margin:0 0 14px; font-family:'JetBrains Mono', Menlo, Consolas, monospace; font-size:11px; line-height:1.4; letter-spacing:0.16em; text-transform:uppercase; color:#5b6669;">
        <span style="color:#ff5a1f;">&#9679;</span>&nbsp; {{ $eyebrow }}
    </p>
@endif
<h1 class="pl-h1" style="margin:0 0 20px; font-family:'Manrope', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:28px; line-height:1.2; font-weight:700; letter-spacing:-0.025em; color:#0f1d22;">
    {{ $slot }}
</h1>
