<?php

namespace App\Console\Commands\Backup;

use App\Models\BackupRun;
use App\Services\Backup\BackupException;
use App\Services\Backup\BackupFiles;
use App\Services\Backup\BackupRestorer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class BackupRestoreTestCommand extends Command
{
    // php artisan backup:restore-test                → neuestes Backup in die isolierte Test-Datenbank
    // php artisan backup:restore-test datei.zip --drop
    protected $signature = 'backup:restore-test
        {file? : Dateiname oder Pfad (Standard: neuestes lokales Backup)}
        {--database= : Ziel-Datenbank (Standard: BACKUP_RESTORE_TEST_DATABASE)}
        {--keep-files : Wiederhergestellte Dateien nach der Prüfung behalten}
        {--drop : Test-Datenbank nach der Prüfung wieder löschen}
        {--trigger=cli}
        {--run-id=}';

    protected $description = 'Spielt ein Backup in eine ISOLIERTE Test-Datenbank ein und prüft Tabellen, Datenmengen, Beziehungen und Dateien';

    public function handle(BackupRestorer $restorer): int
    {
        try {
            $path = BackupFiles::resolve($this->argument('file'));
        } catch (BackupException $e) {
            $this->error($e->getMessage());
            BackupRun::adoptOrBegin($this->option('run-id'), 'restore-test', $this->option('trigger'))?->finish('failed', $e->getMessage());
            return $e->getCode();
        }

        $filesTarget = BackupFiles::directory() . DIRECTORY_SEPARATOR . 'restore-test' . DIRECTORY_SEPARATOR . pathinfo($path, PATHINFO_FILENAME);
        $run = BackupRun::adoptOrBegin($this->option('run-id'), 'restore-test', $this->option('trigger'), basename($path));

        $this->line('Wiederherstellungstest mit ' . basename($path));

        try {
            File::deleteDirectory($filesTarget);

            $result = $restorer->restore($path, BackupRestorer::TEST, [
                'database' => $this->option('database') ?: null,
                'files_target' => $filesTarget,
            ]);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $run?->finish('failed', $e->getMessage());
            File::deleteDirectory($filesTarget);

            return $e instanceof BackupException ? $e->getCode() : BackupException::RESTORE;
        }

        $checks = $result['checks'];
        $this->table(['Prüfung', 'Ergebnis'], array_filter([
            ['Ziel-Datenbank', $result['database']],
            ['Tabellen', ($checks['tables']['restored'] ?? 0) . ' / ' . ($checks['tables']['expected'] ?? 0)],
            ['Datensätze (exakt)', number_format($checks['rows'] ?? 0, 0, ',', '.')],
            ['Fremdschlüssel geprüft', $checks['foreign_keys'] ?? 0],
            ['AUTO_INCREMENT geprüft', $checks['auto_increment'] ?? 0],
            ['Dateien geschrieben / übersprungen', $result['files']['written'] . ' / ' . $result['files']['skipped']],
            isset($checks['media_files_missing']) ? ['Fehlende Bilddateien', $checks['media_files_missing']] : null,
            isset($checks['migrations']) ? ['Migrationen (ausgeführt/offen/unbekannt)', implode(' / ', $checks['migrations'])] : null,
            isset($checks['smoke']) ? ['Funktionsprobe', collect($checks['smoke'])->map(fn($v, $k) => "{$k}: {$v}")->implode(', ')] : null,
        ]));

        foreach ($result['warnings'] as $warning) {
            $this->warn("⚠ {$warning}");
        }
        foreach ($result['errors'] as $error) {
            $this->error("✘ {$error}");
        }

        if (! $this->option('keep-files')) {
            File::deleteDirectory($filesTarget);
        } else {
            $this->line("Dateien: {$filesTarget}");
        }

        if ($this->option('drop')) {
            $restorer->dropTestDatabase($result['database']);
            $this->line("Test-Datenbank {$result['database']} gelöscht.");
        } else {
            $this->line("Die Test-Datenbank „{$result['database']}“ bleibt zur Kontrolle bestehen (löschen mit --drop).");
        }

        $run?->finish($result['status'], $result['ok'] ? 'Wiederherstellung erfolgreich geprüft.' : implode("\n", $result['errors']), [
            'details' => ['checks' => $checks, 'warnings' => $result['warnings'], 'errors' => $result['errors']],
        ]);

        if (! $result['ok']) {
            return BackupException::RESTORE;
        }

        $this->info('✔ Wiederherstellung erfolgreich geprüft.');

        return self::SUCCESS;
    }
}
