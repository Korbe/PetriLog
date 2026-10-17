@props(['muted' => false])
<p style="margin:0 0 16px; font-size:16px; line-height:1.6; color:{{ $muted ? '#5b6669' : '#0f1d22' }};">
    {{ $slot }}
</p>
