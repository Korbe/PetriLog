<?php

namespace App\Console\Commands\Backup;

use App\Models\BackupRun;
use App\Services\Backup\BackupException;
use App\Services\Backup\BackupFiles;
use App\Services\Backup\BackupVerifier;
use Illuminate\Console\Command;

class BackupVerifyCommand extends Command
{
    // php artisan backup:verify                     → neuestes lokales Backup
    // php artisan backup:verify /pfad/zu/backup.zip → z. B. vom Backup-Server geholte Datei
    // --uploaded: hochgeladene Datei (noch ohne .sha256) – bei Erfolg Prüfsummendatei anlegen, sonst Datei löschen
    protected $signature = 'backup:verify {file? : Dateiname oder Pfad} {--trigger=cli} {--run-id=} {--uploaded}';

    protected $description = 'Prüft ein Backup vollständig (Prüfsumme, Entschlüsselung, SHA-256 jeder Datei)';

    public function handle(BackupVerifier $verifier): int
    {
        try {
            $path = BackupFiles::resolve($this->argument('file'));
        } catch (BackupException $e) {
            $this->error($e->getMessage());
            BackupRun::adoptOrBegin($this->option('run-id'), 'verify', $this->option('trigger'))?->finish('failed', $e->getMessage());
            return $e->getCode();
        }

        $run = BackupRun::adoptOrBegin($this->option('run-id'), 'verify', $this->option('trigger'), basename($path));
        $this->line('Prüfe ' . basename($path) . ' …');

        $uploaded = (bool) $this->option('uploaded');
        $result = $verifier->verify($path, requireChecksumFile: ! $uploaded);

        if (! $result['ok']) {
            foreach ($result['errors'] as $error) {
                $this->error("✘ {$error}");
            }
            // Ein ungültiger Upload darf nie als Backup in der Liste bleiben
            if ($uploaded) {
                BackupFiles::delete($path);
                $result['errors'][] = 'Hochgeladene Datei wurde wieder entfernt.';
            }
            $run?->finish('failed', implode("\n", $result['errors']), ['sha256' => $result['sha256']]);
            return BackupException::VERIFY;
        }

        if ($uploaded && BackupFiles::readChecksumFile($path) === null) {
            BackupFiles::writeChecksumFile($path, $result['sha256']);
        }

        $manifest = $result['manifest'];
        $this->table(['Eigenschaft', 'Wert'], [
            ['Erstellt (UTC)', $manifest['created_at']],
            ['Code-Stand', $manifest['revision'] ?? '–'],
            ['Verschlüsselt', $manifest['encrypted'] ? 'ja (AES-256)' : 'nein'],
            ['Tabellen', count($manifest['database']['tables'])],
            ['Datensätze', number_format(array_sum($manifest['database']['tables']), 0, ',', '.')],
            ['Dateien', $manifest['files']['count'] . ' (' . number_format($manifest['files']['size'] / 1048576, 1, ',', '.') . ' MB)'],
            ['SHA-256', $result['sha256']],
        ]);

        foreach ($manifest['warnings'] ?? [] as $warning) {
            $this->warn("⚠ {$warning}");
        }

        $run?->finish('success', 'Backup ist vollständig und unbeschädigt.', ['sha256' => $result['sha256'], 'size' => filesize($path)]);
        $this->info('✔ Backup ist vollständig und unbeschädigt.');

        return self::SUCCESS;
    }
}
