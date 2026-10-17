<?php

namespace App\Console\Commands\Backup;

use App\Services\Backup\BackupRunner;
use App\Services\Backup\RemoteBackupStore;
use App\Services\Backup\RetentionPolicy;
use Illuminate\Console\Command;
use Throwable;

class BackupPruneCommand extends Command
{
    protected $signature = 'backup:prune {--remote : Aufbewahrung auch auf dem Backup-Server durchsetzen}';

    protected $description = 'Löscht alte Backups gemäß Aufbewahrungsregeln (lokal nur, wenn sicher übertragen)';

    public function handle(BackupRunner $runner, RemoteBackupStore $remote): int
    {
        $local = $runner->pruneLocal();
        $this->line($local ? 'Lokal gelöscht: ' . implode(', ', $local) : 'Lokal: nichts zu löschen.');

        if ($this->option('remote')) {
            if (! RemoteBackupStore::enabled()) {
                $this->warn('Zweitserver ist nicht aktiviert.');
                return self::SUCCESS;
            }

            try {
                $result = $remote->prune(RetentionPolicy::fromConfig());
                $this->line('Remote behalten: ' . count($result['keep']) . ', gelöscht: ' . (implode(', ', $result['delete']) ?: 'keine'));
            } catch (Throwable $e) {
                $this->error('Remote-Aufräumen fehlgeschlagen: ' . $e->getMessage());
                return 7;
            }
        }

        return self::SUCCESS;
    }
}
