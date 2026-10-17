@props([
    // Vorschautext, den Mail-Clients neben dem Betreff zeigen
    'preheader' => null,
    'signoff' => true,
])
@php
    $sans = "'Schibsted Grotesk', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif";
    $display = "'Manrope', 'Helvetica Neue', Helvetica, Arial, sans-serif";
@endphp
<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ config('app.name') }}</title>
    <link href="https://fonts.bunny.net/css?family=manrope:600,700|schibsted-grotesk:400,500,600&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; }
        a { color: #0c6fc0; }

        @media only screen and (max-width: 620px) {
            .pl-outer { padding: 16px 8px !important; }
            .pl-pad { padding-left: 22px !important; padding-right: 22px !important; }
            .pl-h1 { font-size: 24px !important; }
            .pl-button a { display: block !important; }
        }
    </style>
</head>

<body style="margin:0; padding:0; background-color:#f3eee3;">
    @if ($preheader)
        <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#f3eee3;">
            {{ $preheader }}&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
        </div>
    @endif

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f3eee3;">
        <tr>
            <td align="center" class="pl-outer" style="padding:32px 12px;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;">

                    {{-- Header: dunkles Wasser wie auf der Website --}}
                    <tr>
                        <td class="pl-pad" style="background-color:#06212b; border-radius:18px 18px 0 0; padding:22px 36px;">
                            <table cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <a href="{{ url('/') }}" style="text-decoration:none;">
                                            <img src="{{ asset('logo-short.png') }}" width="30" height="30" alt=""
                                                style="display:inline-block; vertical-align:middle; border:0; width:30px; height:30px;">
                                            <span style="display:inline-block; vertical-align:middle; margin-left:8px; font-family:{{ $display }}; font-size:21px; font-weight:600; letter-spacing:-0.02em; color:#f3eee3;">PetriLog</span>
                                        </a>
                                    </td>
                                    <td align="right" style="vertical-align:middle; font-family:'JetBrains Mono', Menlo, Consolas, monospace; font-size:10px; letter-spacing:0.18em; text-transform:uppercase; color:#8fa6ad;">
                                        Fangbuch
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Inhalt --}}
                    <tr>
                        <td class="pl-pad" style="background-color:#ffffff; border-radius:0 0 18px 18px; border:1px solid #e6e0d3; border-top:0; padding:36px 36px 32px; font-family:{{ $sans }}; font-size:16px; line-height:1.6; color:#0f1d22;">
                            {{ $slot }}

                            @if ($signoff)
                                <p style="margin:32px 0 0; font-size:16px; line-height:1.5; color:#0f1d22;">
                                    Petri Heil 🎣<br>
                                    <span style="color:#5b6669;">Dein {{ config('app.name') }}-Team</span>
                                </p>
                            @endif

                            @isset($subcopy)
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top:28px;">
                                    <tr>
                                        <td style="border-top:1px solid #ece7dc; padding-top:20px; font-size:13px; line-height:1.55; color:#5b6669;">
                                            {{ $subcopy }}
                                        </td>
                                    </tr>
                                </table>
                            @endisset
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:26px 24px 8px; font-family:{{ $sans }}; font-size:12px; line-height:1.7; color:#7a8588;">
                            <a href="{{ url('/') }}" style="color:#5b6669; text-decoration:none;">petrilog.com</a>
                            &nbsp;·&nbsp;
                            <a href="{{ route('public.imprint') }}" style="color:#5b6669; text-decoration:none;">Impressum</a>
                            &nbsp;·&nbsp;
                            <a href="{{ route('public.policy') }}" style="color:#5b6669; text-decoration:none;">Datenschutz</a>
                            <br>
                            Das digitale Fangbuch für Angler in Österreich · © {{ date('Y') }} {{ config('app.name') }}
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
