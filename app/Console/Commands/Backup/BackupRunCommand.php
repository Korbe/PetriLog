<?php

namespace App\Console\Commands\Backup;

use App\Models\BackupRun;
use App\Services\Backup\BackupRunner;
use Illuminate\Console\Command;

/**
 * Einstiegspunkt für Cron / systemd-Timer:
 *   cd /var/www/petrilog.com/current && php artisan backup:run --trigger=schedule
 *
 * Exit-Codes: 0 OK · 1 Fehler · 2 Konfiguration · 3 läuft bereits · 4 Datenbank · 5 Archiv · 6 Prüfung · 7 Übertragung
 */
class BackupRunCommand extends Command
{
    protected $signature = 'backup:run
        {--no-transfer : Nicht auf den Backup-Server übertragen}
        {--download : Nur zum Herunterladen (Weboberfläche): keine Übertragung, Kopie wird nachts wieder gelöscht}
        {--restore-test : Das neue Backup zusätzlich testweise in die isolierte Test-Datenbank einspielen}
        {--trigger=cli : cli | schedule | web (nur fürs Protokoll)}
        {--run-id= : Vorhandenen Statuseintrag fortführen (Weboberfläche)}';

    protected $description = 'Vollständiges Backup erstellen, prüfen, auf den Zweitserver übertragen und alte Backups aufräumen';

    public function handle(BackupRunner $runner): int
    {
        $trigger = in_array($this->option('trigger'), ['cli', 'schedule', 'web'], true) ? $this->option('trigger') : 'cli';
        $run = $this->option('run-id') ? BackupRun::find($this->option('run-id')) : null;

        $result = $runner->run(
            trigger: $trigger,
            userId: $run?->user_id,
            transfer: ! $this->option('no-transfer') && ! $this->option('download'),
            output: fn(string $line) => $this->line($line),
            run: $run,
            restoreTest: (bool) $this->option('restore-test'),
            downloadOnly: (bool) $this->option('download'),
        );

        if ($result['ok']) {
            $this->info($result['warnings'] ? 'Backup abgeschlossen (mit Hinweisen).' : 'Backup abgeschlossen.');
            return self::SUCCESS;
        }

        $this->error("Backup fehlgeschlagen (Code {$result['code']}): {$result['message']}");

        return $result['code'];
    }
}
