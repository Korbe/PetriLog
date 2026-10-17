<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Fangmeldung {{ $year }} – {{ $user->name }} – PetriLog</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet"
        href="https://fonts.bunny.net/css?family=manrope:600|schibsted-grotesk:400,600|jetbrains-mono:400&display=swap">
    <style>
        :root {
            --ink: #0f1d22;
            --muted: #5b676b;
            --line: #d9d4c7;
            --paper: #f4f1ea;
            --accent: #118df0;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font: 13px/1.45 "Schibsted Grotesk", "Segoe UI", system-ui, sans-serif;
        }

        .sheet {
            max-width: 210mm;
            margin: 24px auto;
            padding: 18mm 16mm;
            background: #fff;
            box-shadow: 0 10px 40px rgba(15, 29, 34, .08);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            padding-bottom: 14px;
            border-bottom: 2px solid var(--ink);
        }

        h1 { margin: 0; font: 600 30px/1.05 "Manrope", "Segoe UI", system-ui, sans-serif; letter-spacing: -0.035em; }
        h2 { margin: 28px 0 8px; font-size: 15px; }
        .kicker { margin: 0 0 6px; font: 11px/1 "JetBrains Mono", Consolas, monospace; letter-spacing: .14em; text-transform: uppercase; color: var(--muted); }
        .meta { text-align: right; color: var(--muted); }
        .meta strong { color: var(--ink); }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th:first-child, td:first-child { width: 30%; }
        table.list th:first-child, table.list td:first-child { width: 19%; }
        table.list th:nth-child(2), table.list td:nth-child(2),
        table.list th:nth-child(3), table.list td:nth-child(3) { width: 21%; }
        th, td { padding: 6px 8px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
        th { font: 600 10px/1.2 "JetBrains Mono", Consolas, monospace; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        tfoot td { font-weight: 600; border-bottom: 0; border-top: 1px solid var(--ink); }

        .empty { padding: 32px 0; color: var(--muted); text-align: center; }

        .sign { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 48px; }
        .sign div { padding-top: 6px; border-top: 1px solid var(--ink); color: var(--muted); font-size: 11px; }

        footer { margin-top: 28px; color: var(--muted); font-size: 10px; }

        .toolbar { max-width: 210mm; margin: 24px auto 0; display: flex; gap: 8px; justify-content: flex-end; }
        .toolbar button, .toolbar a {
            border: 1px solid var(--line); border-radius: 999px; background: #fff; color: var(--ink);
            padding: 9px 18px; font: 600 13px/1 inherit; cursor: pointer; text-decoration: none;
        }
        .toolbar button { background: var(--accent); border-color: var(--accent); color: #fff; }

        @page { size: A4; margin: 14mm 12mm; }

        @media print {
            body { background: #fff; }
            .sheet { margin: 0; padding: 0; box-shadow: none; max-width: none; }
            .toolbar { display: none; }
            tr { break-inside: avoid; }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <a href="{{ route('app.report.index', ['year' => $year]) }}">Zurück</a>
        <button type="button" onclick="window.print()">Drucken / als PDF speichern</button>
    </div>

    <main class="sheet">
        <header>
            <div>
                <p class="kicker">PetriLog · Fangmeldung</p>
                <h1>Fangstatistik {{ $year }}</h1>
            </div>
            <div class="meta">
                <div><strong>{{ $user->name }}</strong></div>
                <div>{{ $user->email }}</div>
                @if ($waterName)
                    <div>Gewässer: <strong>{{ $waterName }}</strong></div>
                @endif
            </div>
        </header>

        @forelse ($summary as $water)
            <h2>{{ $water['water'] }}</h2>
            <table>
                <thead>
                    <tr>
                        <th>Fischart</th>
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
            <table class="list">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Fischart</th>
                        <th>Gewässer</th>
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

        <div class="sign">
            <div>Ort, Datum</div>
            <div>Unterschrift</div>
        </div>

        <footer>Erstellt am {{ now($timezone)->format('d.m.Y') }} mit PetriLog · petrilog.com</footer>
    </main>
</body>

</html>
