<?php

namespace App\Console\Commands;

use App\Models\PageView;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Schlüsselt die Zugriffsstatistik auf – um ungewöhnliche Spitzen zu verstehen (Bots, Link-Scanner, Newsletter-Klicks).
 */
class TrafficReport extends Command
{
    protected $signature = 'stats:traffic {--days=2 : Zeitraum in Tagen (inkl. heute)}';

    protected $description = 'Zugriffsstatistik aufschlüsseln: Seiten, Uhrzeiten, eingeloggt/Gast, Herkunft, Geräte';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $from = now('Europe/Vienna')->subDays($days - 1)->toDateString();

        // Wenige tausend Zeilen – in PHP auswerten, das läuft in MySQL und SQLite gleich
        $views = PageView::where('date', '>=', $from)
            ->get(['date', 'visitor', 'authenticated', 'route', 'path', 'referrer_host', 'device', 'created_at']);

        // Besucher werden je Tag gezählt (der Kurzwert wechselt täglich)
        $visitors = fn(Collection $rows) => $rows->map(fn($v) => $v->date . $v->visitor)->unique()->count();

        $this->info("Seit {$from}: {$views->count()} Seitenaufrufe von {$visitors($views)} Besuchern (Besucher je Tag gezählt)");

        // Viele Besucher mit genau einem Aufruf (ohne Herkunft) sprechen für Scanner/Bots
        $perVisitor = $views->countBy(fn($v) => $v->date . $v->visitor);
        $this->line("Besucher mit nur 1 Aufruf: {$perVisitor->filter(fn($n) => $n === 1)->count()} von {$perVisitor->count()}");
        $this->newLine();

        $table = function (string $label, callable $key, int $limit = 12) use ($views, $visitors) {
            $this->table([$label, 'Aufrufe', 'Besucher'], $views->groupBy($key)
                ->map(fn(Collection $rows, $value) => [$value, $rows->count(), $visitors($rows)])
                ->sortByDesc(fn($row) => $row[1])
                ->take($limit)
                ->values());
        };

        $table('Eingeloggt', fn($v) => $v->authenticated ? 'ja' : 'nein (Gast)');
        $table('Seite', fn($v) => $v->route ?? $v->path);
        $table('Herkunft', fn($v) => $v->referrer_host ?? '(direkt / Mail-Programm)', 8);
        $table('Gerät', fn($v) => $v->device ?? '(vor Einführung)');

        // Uhrzeit (Wien): Spitzen direkt nach einem Newsletter-Versand deuten auf Link-Scanner der Mail-Anbieter
        $this->table(['Stunde', 'Aufrufe', 'Besucher'], $views
            ->groupBy(fn($v) => $v->created_at->timezone('Europe/Vienna')->format('Y-m-d H') . ':00')
            ->sortKeys()
            ->map(fn(Collection $rows, $hour) => [$hour, $rows->count(), $visitors($rows)])
            ->values());

        return self::SUCCESS;
    }
}
