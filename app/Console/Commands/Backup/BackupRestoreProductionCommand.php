<?php

namespace App\Console\Commands\Backup;

use App\Models\BackupRun;
use App\Services\Backup\BackupException;
use App\Services\Backup\BackupFiles;
use App\Services\Backup\BackupRestorer;
use App\Services\Backup\BackupRunner;
use App\Services\Backup\RestoreMaintenance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Wiederherstellung in die PRODUKTIVE Datenbank – mit Bestätigung, Sicherheits-Backup und Wartungsmodus.
 * Für Tests immer backup:restore-test verwenden.
 */
class BackupRestoreProductionCommand extends Command
{
    protected $signature = 'backup:restore-production
        {file : Dateiname oder Pfad des Backups}
        {--confirm= : Name der produktiven Datenbank zur Bestätigung (für nicht-interaktive Ausführung)}
        {--no-files : Nur die Datenbank wiederherstellen}
        {--skip-safety-backup : Kein Sicherheits-Backup des aktuellen Stands vorher}
        {--maintenance=laravel : laravel (php artisan down) | flag (Website-Sperre, Queue läuft weiter – Weboberfläche)}
        {--trigger=cli}
        {--run-id=}';

    protected $description = 'Stellt ein Backup in die PRODUKTIVE Datenbank und den Storage wieder her (mit Bestätigung)';

    public function handle(BackupRestorer $restorer, BackupRunner $runner): int
    {
        $connection = config('backup.database.connection');
        $database = config("database.connections.{$connection}.database");
        $trigger = $this->option('trigger');
        // Eintrag der Weboberfläche – existiert nur bis zum Import, danach ist die Tabelle ersetzt
        $queued = BackupRun::adoptOrBegin($this->option('run-id'), 'restore-production', $trigger);

        try {
            $path = BackupFiles::resolve($this->argument('file'));
        } catch (BackupException $e) {
            $this->error($e->getMessage());
            $queued?->finish('failed', $e->getMessage());
            return $e->getCode();
        }

        $this->warn("ACHTUNG: Die Datenbank „{$database}“ wird mit " . basename($path) . ' überschrieben.');
        if (! $this->option('no-files')) {
            $this->warn('Storage-Dateien werden auf den Stand des Backups gebracht (identische Dateien werden übersprungen).');
        }

        $confirmation = $this->option('confirm') ?? ($this->input->isInteractive() ? $this->ask('Zum Bestätigen den Datenbanknamen eingeben') : null);
        if ($confirmation !== $database) {
            $this->error('Abgebrochen – Datenbankname wurde nicht bestätigt.');
            $queued?->finish('failed', 'Datenbankname wurde nicht bestätigt.', ['file' => basename($path)]);
            return BackupException::RESTORE;
        }

        // Sicherheitsnetz: aktuellen Stand vorher sichern (nur lokal)
        $safetyFile = null;
        if (! $this->option('skip-safety-backup')) {
            $this->line('Erstelle Sicherheits-Backup des aktuellen Stands …');
            $safety = $runner->run(trigger: $trigger, transfer: false, output: fn($line) => $this->line("  {$line}"));
            if (! $safety['ok']) {
                $message = 'Sicherheits-Backup fehlgeschlagen – Wiederherstellung abgebrochen: ' . $safety['message'];
                $this->error($message . ' (--skip-safety-backup, falls die Datenbank zerstört ist)');
                $queued?->finish('failed', $message, ['file' => basename($path)]);
                return $safety['code'];
            }
            $safetyFile = $safety['file'];
        }

        $useFlag = $this->option('maintenance') === 'flag';
        $wasDown = app()->isDownForMaintenance();
        $useFlag ? RestoreMaintenance::enable(basename($path)) : ($wasDown || Artisan::call('down', ['--retry' => 60]));
        $startedAt = now();

        try {
            $result = $restorer->restore($path, BackupRestorer::PRODUCTION, [
                'restore_files' => ! $this->option('no-files'),
            ]);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $this->record('failed', $e->getMessage(), $path, $startedAt, $trigger, ['safety_backup' => $safetyFile]);
            $this->warn($useFlag
                ? "Die Website bleibt gesperrt. Nach Klärung: Datei {$this->relative(RestoreMaintenance::file())} löschen."
                : 'Die Anwendung bleibt im Wartungsmodus. Nach Klärung: php artisan up');
            if ($safetyFile) {
                $this->warn("Sicherheits-Backup des vorherigen Stands: {$safetyFile}");
            }

            return $e instanceof BackupException ? $e->getCode() : BackupException::RESTORE;
        }

        foreach ($result['warnings'] as $warning) {
            $this->warn("⚠ {$warning}");
        }
        foreach ($result['errors'] as $error) {
            $this->error("✘ {$error}");
        }

        if ($result['ok'] && ($result['checks']['migrations']['pending'] ?? 0) > 0) {
            $this->line('Führe ausstehende Migrationen aus …');
            Artisan::call('migrate', ['--force' => true]);
        }

        // Neuer Eintrag – die alte Statuszeile wurde mit der Tabelle backup_runs ersetzt
        $this->record($result['status'], $result['ok'] ? 'Produktiv wiederhergestellt.' : implode("\n", $result['errors']), $path, $startedAt, $trigger, [
            'checks' => $result['checks'], 'files' => $result['files'], 'warnings' => $result['warnings'], 'safety_backup' => $safetyFile,
        ]);

        if (! $result['ok']) {
            $this->warn($useFlag ? 'Die Website bleibt gesperrt, bis die Fehler geklärt sind.' : 'Die Anwendung bleibt im Wartungsmodus. Nach Klärung: php artisan up');
            return BackupException::RESTORE;
        }

        $useFlag ? RestoreMaintenance::disable() : ($wasDown || Artisan::call('up'));

        $this->info("✔ Wiederhergestellt: {$result['files']['written']} Dateien geschrieben, {$result['files']['skipped']} unverändert.");
        if ($safetyFile) {
            $this->line("Vorheriger Stand gesichert in: {$safetyFile}");
        }

        return self::SUCCESS;
    }

    private function record(string $status, string $message, string $path, $startedAt, string $trigger, array $details): void
    {
        try {
            // Das Backup enthält seinen eigenen, damals noch laufenden Statuseintrag – solche Einträge abschließen
            BackupRun::whereIn('status', ['queued', 'running'])->update([
                'status' => 'failed',
                'message' => 'Durch Wiederherstellung unterbrochen (Stand des Backups).',
                'finished_at' => now(),
            ]);

            BackupRun::create([
                'action' => 'restore-production',
                'status' => $status,
                'trigger' => $trigger,
                'file' => basename($path),
                'message' => $message,
                'details' => $details,
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::channel(config('backup.log_channel'))->warning("Status der Wiederherstellung nicht gespeichert: {$e->getMessage()}");
        }

        Log::channel(config('backup.log_channel'))->{$status === 'failed' ? 'error' : 'notice'}(
            "Produktive Wiederherstellung aus " . basename($path) . ": {$status}", ['message' => $message]
        );
    }

    private function relative(string $path): string
    {
        return str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
    }
}
