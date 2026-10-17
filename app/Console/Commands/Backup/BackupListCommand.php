<?php

namespace App\Console\Commands\Backup;

use App\Services\Backup\BackupFiles;
use App\Services\Backup\RemoteBackupStore;
use App\Services\Backup\RetentionPolicy;
use Illuminate\Console\Command;
use Throwable;

class BackupListCommand extends Command
{
    protected $signature = 'backup:list {--remote : Auch die Backups auf dem Backup-Server anzeigen}';

    protected $description = 'Listet lokale (und entfernte) Backups';

    public function handle(RemoteBackupStore $remote): int
    {
        $local = BackupFiles::list();
        $this->info('Lokal: ' . BackupFiles::directory());
        $this->table(['Datei', 'Erstellt (UTC)', 'Größe', 'SHA-256'], $local->map(fn($b) => [
            $b['name'], $b['created_at']?->format('Y-m-d H:i'), number_format($b['size'] / 1048576, 1, ',', '.') . ' MB', $b['sha256'] ? substr($b['sha256'], 0, 16) . '…' : '–',
        ])->all());

        if ($this->option('remote')) {
            if (! RemoteBackupStore::enabled()) {
                $this->warn('Zweitserver ist nicht aktiviert.');
                return self::SUCCESS;
            }

            try {
                $backups = $remote->list();
                $plan = RetentionPolicy::fromConfig()->apply($backups);
                arsort($backups);
                $this->info('Backup-Server: ' . config('backup.remote.host') . ':' . config('backup.remote.root'));
                $this->table(['Datei', 'Erstellt (UTC)', 'Aufbewahrung'], collect($backups)->map(fn($date, $name) => [
                    $name, $date->format('Y-m-d H:i'), in_array($name, $plan['keep'], true) ? 'behalten' : 'fällt unter Aufbewahrung',
                ])->values()->all());
            } catch (Throwable $e) {
                $this->error('Backup-Server nicht erreichbar: ' . $e->getMessage());
                return 7;
            }
        }

        return self::SUCCESS;
    }
}
