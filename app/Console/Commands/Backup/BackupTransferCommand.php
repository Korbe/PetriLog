<?php

namespace App\Console\Commands\Backup;

use App\Services\Backup\BackupException;
use App\Services\Backup\BackupFiles;
use App\Services\Backup\BackupRunner;
use App\Services\Backup\RemoteBackupStore;
use Illuminate\Console\Command;
use Throwable;

class BackupTransferCommand extends Command
{
    // php artisan backup:transfer [datei] – z. B. zum Nachholen nach einem Netzwerkfehler
    protected $signature = 'backup:transfer {file? : Dateiname (Standard: neuestes Backup)}';

    protected $description = 'Überträgt ein lokales Backup per SFTP auf den Backup-Server und prüft die SHA-256-Prüfsumme';

    public function handle(BackupRunner $runner): int
    {
        if (! RemoteBackupStore::enabled()) {
            $this->error('Zweitserver ist nicht aktiviert (BACKUP_REMOTE_ENABLED).');
            return BackupException::CONFIG;
        }

        try {
            $path = BackupFiles::resolve($this->argument('file'));
            $this->line('Übertrage ' . basename($path) . ' …');
            $result = $runner->transfer($path);
            $this->info($result['uploaded']
                ? "✔ Übertragen, SHA-256 auf dem Backup-Server bestätigt ({$result['sha256']})"
                : '✔ War bereits vollständig auf dem Backup-Server vorhanden.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return $e instanceof BackupException ? $e->getCode() : BackupException::TRANSFER;
        }
    }
}
