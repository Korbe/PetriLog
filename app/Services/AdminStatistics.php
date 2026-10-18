<?php

namespace App\Services;

use App\Http\Controllers\SubmissionController;
use App\Models\Catched;
use App\Models\MailLog;
use App\Models\PageView;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Kennzahlen für das Admin-Dashboard: Traffic, Mails, Fänge, User – jeweils mit Vergleich zum Vorzeitraum.
 */
class AdminStatistics
{
    public const PERIODS = [7, 30, 90, 365, 1095];

    private const TIMEZONE = 'Europe/Vienna';

    private CarbonImmutable $start;
    private CarbonImmutable $end;
    private CarbonImmutable $previousStart;

    public function __construct(private int $days)
    {
        $today = CarbonImmutable::now(self::TIMEZONE)->startOfDay();
        $this->end = $today->endOfDay();
        $this->start = $today->subDays($days - 1);
        $this->previousStart = $this->start->subDays($days);
    }

    public function toArray(): array
    {
        return [
            'period' => $this->days,
            'range' => ['from' => $this->start->toDateString(), 'to' => $this->end->toDateString()],
            'kpis' => $this->kpis(),
            'series' => $this->series(),
            'mailsByType' => $this->mailsByType(),
            'recentMails' => $this->recentMails(),
            'topPages' => $this->topPages(),
            'topReferrers' => $this->topReferrers(),
            'devices' => $this->devices(),
            'topFish' => $this->topFish(),
            'totals' => $this->totals(),
            'health' => $this->health(),
        ];
    }

    private function kpis(): array
    {
        $metric = fn(callable $count) => [
            'value' => $count($this->start, $this->end),
            'previous' => $count($this->previousStart, $this->start->subSecond()),
        ];

        return [
            'views' => $metric(fn($from, $to) => $this->pageViews($from, $to)->count()),
            'visitors' => $metric(fn($from, $to) => $this->uniqueVisitors($from, $to)),
            'users' => $metric(fn($from, $to) => User::whereBetween('created_at', $this->utc($from, $to))->count()),
            'catches' => $metric(fn($from, $to) => Catched::whereBetween('created_at', $this->utc($from, $to))->count()),
            'photos' => $metric(fn($from, $to) => Media::where('model_type', Catched::class)->whereBetween('created_at', $this->utc($from, $to))->count()),
            'activeUsers' => $metric(fn($from, $to) => Catched::whereBetween('created_at', $this->utc($from, $to))->distinct()->count('user_id')),
            'mails' => $metric(fn($from, $to) => MailLog::whereBetween('sent_at', $this->utc($from, $to))->count()),
            'subscriptions' => $metric(fn($from, $to) => DB::table('subscriptions')->whereBetween('created_at', $this->utc($from, $to))->count()),
        ];
    }

    /**
     * Tageswerte; bei einem Jahr wochenweise, bei drei Jahren monatsweise, damit das Diagramm lesbar bleibt.
     */
    private function series(): array
    {
        $grouping = match (true) {
            $this->days > 365 => 'month',
            $this->days > 90 => 'week',
            default => 'day',
        };
        $bucket = fn(CarbonImmutable $date) => match ($grouping) {
            'month' => $date->startOfMonth()->toDateString(),
            'week' => $date->startOfWeek()->toDateString(),
            default => $date->toDateString(),
        };

        // Tageweise durchgehen und dann zusammenfassen – so fehlt nie die angebrochene letzte Woche bzw. der letzte Monat
        $labels = collect(CarbonPeriod::create($this->start, '1 day', $this->end))
            ->map(fn($d) => $bucket(CarbonImmutable::instance($d)))
            ->unique()
            ->values();

        $count = function (Collection $dates) use ($bucket, $labels) {
            $counts = $dates->countBy(fn($date) => $bucket(CarbonImmutable::parse($date, 'UTC')->setTimezone(self::TIMEZONE)));
            return $labels->map(fn($label) => $counts[$label] ?? 0)->all();
        };

        $views = $this->pageViews($this->start, $this->end)
            ->selectRaw('date, COUNT(*) as views, COUNT(DISTINCT visitor) as visitors')
            ->groupBy('date')
            ->get()
            ->groupBy(fn($row) => $bucket(CarbonImmutable::parse($row->date, self::TIMEZONE)));

        $range = $this->utc($this->start, $this->end);

        return [
            'labels' => $labels->all(),
            'grouping' => $grouping,
            'views' => $labels->map(fn($l) => (int) ($views[$l] ?? collect())->sum('views'))->all(),
            'visitors' => $labels->map(fn($l) => (int) ($views[$l] ?? collect())->sum('visitors'))->all(),
            'catches' => $count(Catched::whereBetween('created_at', $range)->pluck('created_at')),
            'users' => $count(User::whereBetween('created_at', $range)->pluck('created_at')),
            'mails' => $count(MailLog::whereBetween('sent_at', $range)->pluck('sent_at')),
        ];
    }

    private function mailsByType(): array
    {
        return MailLog::whereBetween('sent_at', $this->utc($this->start, $this->end))
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => ['type' => $row->type, 'label' => MailLog::label($row->type), 'total' => (int) $row->total])
            ->all();
    }

    private function recentMails(): array
    {
        return MailLog::latest('sent_at')->limit(12)->get()
            ->map(fn(MailLog $mail) => [
                'id' => $mail->id,
                'label' => MailLog::label($mail->type),
                'subject' => $mail->subject,
                'recipient' => $mail->recipient,
                'sent_at' => $mail->sent_at,
            ])
            ->all();
    }

    private function topPages(): array
    {
        return $this->pageViews($this->start, $this->end)
            ->selectRaw('COALESCE(route, path) as page, COUNT(*) as views, COUNT(DISTINCT visitor) as visitors')
            ->groupBy('page')
            ->orderByDesc('views')
            ->limit(8)
            ->get()
            ->map(fn($row) => ['page' => $this->pageLabel($row->page), 'views' => (int) $row->views, 'visitors' => (int) $row->visitors])
            ->all();
    }

    private function topReferrers(): array
    {
        return $this->pageViews($this->start, $this->end)
            ->whereNotNull('referrer_host')
            ->selectRaw('referrer_host as host, COUNT(*) as views')
            ->groupBy('host')
            ->orderByDesc('views')
            ->limit(6)
            ->get()
            ->map(fn($row) => ['host' => $row->host, 'views' => (int) $row->views])
            ->all();
    }

    /**
     * Geräteverteilung nach Besuchern (je Tag gezählt, wie die Besucher-Kennzahl) plus Anteil der App-Nutzung.
     * Aufrufe von vor der Einführung (device = null) bleiben außen vor.
     */
    private function devices(): array
    {
        $visitors = fn($query) => (int) $query->selectRaw('COUNT(DISTINCT visitor) as v')->groupBy('date')->get()->sum('v');

        $rows = collect(['mobile' => 'Handy', 'tablet' => 'Tablet', 'desktop' => 'Desktop'])
            ->map(fn(string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'visitors' => $visitors($this->pageViews($this->start, $this->end)->where('device', $key)),
                'views' => $this->pageViews($this->start, $this->end)->where('device', $key)->count(),
            ])
            ->values();

        return [
            'rows' => $rows->all(),
            'total' => $rows->sum('visitors'),
            'appVisitors' => $visitors($this->pageViews($this->start, $this->end)->where('standalone', true)),
        ];
    }

    private function topFish(): array
    {
        return Catched::query()
            ->whereBetween('catcheds.created_at', $this->utc($this->start, $this->end))
            ->join('fish', 'fish.id', '=', 'catcheds.fish_id')
            ->selectRaw('fish.name as name, COUNT(*) as total')
            ->groupBy('fish.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($row) => ['name' => $row->name, 'total' => (int) $row->total])
            ->all();
    }

    private function totals(): array
    {
        return [
            'users' => User::count(),
            'verifiedUsers' => User::whereNotNull('email_verified_at')->count(),
            'subscribers' => DB::table('subscriptions')->whereIn('stripe_status', ['active', 'trialing'])->distinct()->count('user_id'),
            'catches' => Catched::count(),
            'spots' => Schema::hasTable('spots') ? DB::table('spots')->count() : 0,
            'tickets' => Ticket::count(),
            'pendingSubmissions' => collect(SubmissionController::TYPES)->sum(fn(string $model) => $model::pending()->count()),
            'openCorrections' => \App\Models\Correction::open()->count(),
        ];
    }

    /**
     * Mails, Wetterdaten und Bildoptimierung laufen über die Queue – staut sie sich, fehlen sie.
     */
    private function health(): array
    {
        $lastBackup = \App\Models\BackupRun::lastSuccessfulBackupAt();
        // Nur warnen, wo Backups eingerichtet sind (nicht lokal ohne Zweitserver/Zeitplan)
        $backupsExpected = \App\Services\Backup\RemoteBackupStore::enabled() || config('backup.schedule.enabled');

        return [
            'backupAgeHours' => $backupsExpected ? ($lastBackup ? (int) $lastBackup->diffInHours(now()) : -1) : null,
            'queuedJobs' => DB::table('jobs')->count(),
            'oldestJobMinutes' => ($oldest = DB::table('jobs')->min('created_at')) ? (int) floor((time() - (int) $oldest) / 60) : null,
            'failedJobs' => DB::table('failed_jobs')->count(),
            'failedJobsRecent' => DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count(),
            'trackingSince' => PageView::min('date'),
            'mailLogSince' => MailLog::min('sent_at'),
        ];
    }

    private function pageViews(CarbonImmutable $from, CarbonImmutable $to)
    {
        return PageView::query()->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
    }

    // Besucher je Tag summiert – der Hash wechselt täglich, ein Besucher über mehrere Tage zählt also mehrfach
    private function uniqueVisitors(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return (int) $this->pageViews($from, $to)
            ->selectRaw('COUNT(DISTINCT visitor) as visitors')
            ->groupBy('date')
            ->get()
            ->sum('visitors');
    }

    /**
     * Zeitraum in österreichischer Zeit → UTC-Grenzen für created_at-Spalten.
     */
    private function utc(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return [$from->utc()->toDateTimeString(), $to->utc()->toDateTimeString()];
    }

    private function pageLabel(string $page): string
    {
        return [
            'public.index' => 'Startseite',
            'public.pricing' => 'Preise',
            'public.contact' => 'Kontakt',
            'public.imprint' => 'Impressum',
            'public.policy' => 'Datenschutz',
            'public.terms' => 'AGB',
            'public.catched.show' => 'Geteilter Fang',
            'login' => 'Login',
            'register' => 'Registrierung',
            'app.dashboard' => 'Dashboard',
            'app.pwa.dashboard' => 'Dashboard (App)',
            'app.catched.index' => 'Fänge',
            'app.catched.show' => 'Fang-Detail',
            'app.catched.create' => 'Fang eintragen',
            'app.catched.edit' => 'Fang bearbeiten',
            'app.gallery.index' => 'Galerie',
            'app.spots.index' => 'Spot-Karte',
            'app.report.index' => 'Fangmeldung',
            'app.states.index' => 'Gewässer-Wiki',
            'app.states.show' => 'Bundesland',
            'app.lakes.show' => 'See',
            'app.rivers.show' => 'Fluss',
            'app.associations.show' => 'Verein',
            'app.fish.index' => 'Fisch-Wiki',
            'app.fish.show' => 'Fisch-Steckbrief',
            'app.ticket.create' => 'Ticket erstellen',
            'app.profile.show' => 'Profil',
            'app.welcome' => 'Willkommen',
        ][$page] ?? $page;
    }
}
