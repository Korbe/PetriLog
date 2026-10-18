<?php

namespace App\Console\Commands\Backup;

use App\Models\BackupRun;
use App\Services\Backup\BackupFiles;
use App\Services\Backup\BackupRunner;
use App\Services\Backup\RemoteBackupStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Lagebild ohne Datenbank-Kenntnisse: Läuft ein Backup? Hängt etwas? Wann war das letzte erfolgreiche Backup?
 * Räumt hängengebliebene Statuseinträge dabei automatisch auf (wie die Weboberfläche).
 *
 * Exit-Code 0 = alles in Ordnung, 1 = Warnung (für Monitoring/Cron geeignet).
 */
class BackupStatusCommand extends Command
{
    protected $signature = 'backup:status';

    protected $description = 'Zeigt, ob gerade ein Backup läuft, ob etwas hängt und wann das letzte erfolgreiche Backup war';

    public function handle(): int
    {
        $warnings = [];
        $rows = [];

        // 1. Hängengebliebene Einträge aufräumen
        $fixed = BackupRun::finalizeStale();
        if ($fixed) {
            $rows[] = ['✔', 'Aufgeräumt', "{$fixed} hängengebliebene(r) Eintrag/Einträge als abgebrochen markiert"];
        }

        // 2. Läuft gerade ein Backup?
        if (BackupRunner::isRunning()) {
            $pid = trim((string) @file_get_contents(BackupFiles::directory() . DIRECTORY_SEPARATOR . '.backup.lock'));
            $rows[] = ['…', 'Backup läuft', 'ja' . ($pid !== '' ? " (Prozess-ID {$pid})" : '')];
        } else {
            $rows[] = ['✔', 'Backup läuft', 'nein'];
        }

        $open = BackupRun::whereIn('status', ['queued', 'running'])->oldest()->get();
        foreach ($open as $run) {
            $since = ($run->started_at ?? $run->created_at)->diffForHumans(short: true);
            $rows[] = ['…', "Offen: {$run->action}", "{$run->status} seit {$since} (Auslöser: {$run->trigger}, Eintrag #{$run->id})"];
        }

        // 3. Letztes erfolgreiches Backup
        $last = BackupRun::lastSuccessfulBackupAt();
        if ($last === null) {
            $rows[] = ['!', 'Letztes Backup', 'noch keines erfolgreich'];
            $warnings[] = 'Es gibt noch kein erfolgreiches Backup.';
        } else {
            $hours = (int) $last->diffInHours(now());
            $ok = $hours <= BackupRun::MAX_BACKUP_AGE_HOURS;
            $rows[] = [$ok ? '✔' : '!', RemoteBackupStore::enabled() ? 'Letztes Backup auf Server B' : 'Letztes Backup',
                $last->timezone('Europe/Vienna')->format('d.m.Y H:i') . " (vor {$hours} Std.)"];
            if (! $ok) {
                $warnings[] = "Das letzte erfolgreiche Backup ist {$hours} Stunden alt – läuft der Cron?";
            }
        }

        $failed = BackupRun::where('status', 'failed')->where('created_at', '>', now()->subDay())->latest()->first();
        if ($failed) {
            $rows[] = ['!', 'Fehler (24 Std.)', "{$failed->action}: " . str($failed->message)->limit(90)];
        }

        // 4. Lokale Backups und Speicherplatz
        $local = BackupFiles::list();
        $free = @disk_free_space(BackupFiles::directory());
        $rows[] = ['✔', 'Backups auf diesem Server', $local->count() . ' (' . number_format($local->sum('size') / 1048576, 1, ',', '.') . ' MB)'];
        if ($free !== false) {
            $freeGb = $free / 1073741824;
            $rows[] = [$freeGb >= 2 ? '✔' : '!', 'Freier Speicher', number_format($freeGb, 1, ',', '.') . ' GB'];
            if ($freeGb < 2) {
                $warnings[] = 'Weniger als 2 GB freier Speicher – Backups können fehlschlagen.';
            }
        }

        // 5. Queue (Backups aus der Weboberfläche, Mails, Wetter …)
        try {
            // Fällig und nicht begonnen = echter Stau; geplante Jobs (Wetter erst 90 Min. nach dem Fang) sind normal
            $due = DB::table('jobs')->whereNull('reserved_at')->where('available_at', '<=', time());
            $jobs = (clone $due)->count();
            $oldest = (clone $due)->min('available_at');
            $scheduled = DB::table('jobs')->where('available_at', '>', time())->count();
            $waiting = $oldest ? (int) floor((time() - (int) $oldest) / 60) : 0;
            $rows[] = [$waiting > 15 ? '!' : '✔', 'Queue', "{$jobs} fällig" . ($jobs ? ", ältester seit {$waiting} Min." : '')
                . ($scheduled ? ", {$scheduled} geplant (z. B. Wetter)" : '')];
            if ($waiting > 15) {
                $warnings[] = "Aufträge warten seit {$waiting} Minuten – läuft der Queue-Worker?";
            }
        } catch (Throwable $e) {
            $rows[] = ['!', 'Queue', 'nicht prüfbar: ' . $e->getMessage()];
        }

        $this->table(['', 'Prüfung', 'Ergebnis'], $rows);

        if ($warnings) {
            foreach ($warnings as $warning) {
                $this->warn("⚠ {$warning}");
            }

            return self::FAILURE;
        }

        $this->info('Alles in Ordnung.');

        return self::SUCCESS;
    }
}
