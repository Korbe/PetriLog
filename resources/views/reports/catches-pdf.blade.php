<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <title>Fangmeldung {{ $year }} – {{ $user->name }}</title>
    {{-- PDF-Vorlage für dompdf: nur Tabellen und einfaches CSS (kein Flexbox/Grid), Schrift DejaVu Sans für Umlaute --}}
    <style>
        @page { margin: 16mm 14mm 18mm; }

        body { margin: 0; color: #0f1d22; font: 9.5pt/1.4 "DejaVu Sans", sans-serif; }

        .kicker { margin: 0 0 4pt; font-size: 7pt; letter-spacing: 1.5pt; text-transform: uppercase; color: #5b676b; }
        h1 { margin: 0; font-size: 20pt; line-height: 1.1; }
        h2 { margin: 18pt 0 6pt; font-size: 11pt; }

        table { width: 100%; border-collapse: collapse; }
        .head td { vertical-align: bottom; padding: 0 0 8pt; border-bottom: 1.5pt solid #0f1d22; }
        .meta { text-align: right; color: #5b676b; }
        .meta strong { color: #0f1d22; }

        .data th, .data td { padding: 4pt 5pt; border-bottom: 0.5pt solid #d9d4c7; text-align: left; vertical-align: top; }
        .data th { font-size: 6.5pt; letter-spacing: 0.6pt; text-transform: uppercase; color: #5b676b; font-weight: bold; }
        .data .num { text-align: right; }
        .data tfoot td { font-weight: bold; border-bottom: 0; border-top: 1pt solid #0f1d22; }
        .data tr { page-break-inside: avoid; }

        .empty { padding: 24pt 0; color: #5b676b; text-align: center; }

        .sign { margin-top: 40pt; }
        .sign td { width: 50%; padding: 4pt 0 0; border-top: 0.75pt solid #0f1d22; color: #5b676b; font-size: 8pt; }
        .sign td.gap { width: 8%; border-top: 0; }

        .footer { position: fixed; bottom: -10mm; left: 0; right: 0; color: #5b676b; font-size: 7pt; }
        .footer .page:after { content: counter(page); }
    </style>
</head>

<body>
    <div class="footer">
        <table>
            <tr>
                <td>Erstellt am {{ now($timezone)->format('d.m.Y') }} mit PetriLog · petrilog.com · Ein Produkt von korbitsch.at</td>
                <td style="text-align: right;">Seite <span class="page"></span></td>
            </tr>
        </table>
    </div>

    <table class="head">
        <tr>
            <td>
                <p class="kicker">PetriLog · Fangmeldung</p>
                <h1>Fangstatistik {{ $year }}</h1>
            </td>
            <td class="meta">
                <strong>{{ $user->name }}</strong><br>
                {{ $user->email }}
                @if ($waterName)
                    <br>Gewässer: <strong>{{ $waterName }}</strong>
                @endif
            </td>
        </tr>
    </table>

    @forelse ($summary as $water)
        <h2>{{ $water['water'] }}</h2>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 30%;">Fischart</th>
                    <th class="num">Gesamt</th>
                    <th class="num">Entnommen</th>
                    <th class="num">Zurückgesetzt</th>
                    <th class="num">Entn. Gewicht</th>
                    <th class="num">Größter</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($water['fish'] as $fish)
                    <tr>
                        <td>{{ $fish['name'] }}</td>
                        <td class="num">{{ $fish['count'] }}</td>
                        <td class="num">{{ $fish['kept'] }}</td>
                        <td class="num">{{ $fish['released'] }}</td>
                        <td class="num">{{ $fish['keptWeight'] ? number_format($fish['keptWeight'] / 1000, 2, ',', '.') . ' kg' : '–' }}</td>
                        <td class="num">{{ $fish['maxLength'] }} cm</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p class="empty">Für {{ $year }} sind keine Fänge eingetragen.</p>
    @endforelse

    @if ($catches->isNotEmpty())
        <h2>Alle Fänge</h2>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 19%;">Datum</th>
                    <th style="width: 21%;">Fischart</th>
                    <th style="width: 24%;">Gewässer</th>
                    <th class="num">Länge</th>
                    <th class="num">Gewicht</th>
                    <th>Verbleib</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($catches as $c)
                    <tr>
                        <td>{{ $c->date->copy()->setTimezone($timezone)->format('d.m.Y H:i') }}</td>
                        <td>{{ $c->fish?->name ?? 'Unbekannter Fisch' }}</td>
                        <td>{{ $waterNameFor($c) }}</td>
                        <td class="num">{{ $c->length }} cm</td>
                        <td class="num">{{ $c->weight ? number_format($c->weight, 0, ',', '.') . ' g' : '–' }}</td>
                        <td>{{ $releaseLabel($c->released) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">{{ $catches->count() }} Fänge</td>
                    <td colspan="3" class="num">
                        {{ $catches->whereStrict('released', false)->count() }} entnommen ·
                        {{ $catches->whereStrict('released', true)->count() }} zurückgesetzt
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

    <table class="sign">
        <tr>
            <td>Ort, Datum</td>
            <td class="gap"></td>
            <td>Unterschrift</td>
        </tr>
    </table>
</body>

</html>
