<?php

namespace App\Services\Backup;

use App\Models\BackupRun;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Kompletter Backup-Lauf für Cron, CLI und Weboberfläche:
 * Sperre → Backup erstellen & prüfen → auf Zweitserver übertragen & prüfen → lokal (und optional remote) aufräumen.
 *
 * Lokale Backups werden erst gelöscht, wenn ihre Übertragung bestätigt ist.
 */
class BackupRunner
{
    // Download-Kopien aus der Weboberfläche löscht der nächste nächtliche Lauf – sofern sie mindestens so alt sind
    // (damit ein gerade laufender Download nicht abbricht)
    public const DOWNLOAD_TTL_HOURS = 1;

    public function __construct(
        private BackupCreator $creator,
        private RemoteBackupStore $remote,
    ) {
    }

    /**
     * @param callable(string): void|null $output Fortschrittsausgabe (CLI)
     * @return array{ok: bool, code: int, file: ?string, message: string, warnings: array}
     */
    public function run(string $trigger = 'cli', ?int $userId = null, bool $transfer = true, ?callable $output = null, ?BackupRun $run = null, bool $restoreTest = false): array
    {
        $say = $output ?? fn(string $line) => null;
        $lock = $this->acquireLock();

        if ($lock === null) {
            $run?->finish('failed', 'Es läuft bereits ein Backup.');
            return ['ok' => false, 'code' => BackupException::LOCKED, 'file' => null, 'message' => 'Es läuft bereits ein Backup.', 'warnings' => []];
        }

        $run ??= BackupRun::begin('create', $trigger, $userId);
        $run?->update(['status' => 'running', 'started_at' => now()]);
        $warnings = [];
        $file = null;

        try {
            // 1. Erstellen + vollständig prüfen
            $say('Erstelle Backup (Datenbank-Schnappschuss + Storage) …');
            $backup = $this->creator->create();
            $file = $backup['name'];
            $warnings = $backup['warnings'];
            $run?->finish($warnings ? 'warning' : 'success', $warnings ? implode("\n", $warnings) : 'Backup erstellt und geprüft.', [
                'file' => $file, 'size' => $backup['size'], 'sha256' => $backup['sha256'],
                'details' => [
                    'tables' => count($backup['manifest']['database']['tables']),
                    'rows' => array_sum($backup['manifest']['database']['tables']),
                    'files' => $backup['manifest']['files']['count'],
                    'encrypted' => $backup['manifest']['encrypted'],
                    // Ohne Übertragung (Knopf in der Weboberfläche): reine Download-Kopie
                    'download_only' => ! $transfer,
                ],
            ]);
            $say("✔ {$file} (" . number_format($backup['size'] / 1048576, 1) . ' MB, SHA-256 ' . substr($backup['sha256'], 0, 12) . '…)');
            foreach ($warnings as $warning) {
                $say("⚠ {$warning}");
            }

            // 2. Übertragen
            if ($transfer && RemoteBackupStore::enabled()) {
                $say('Übertrage auf den Backup-Server …');
                $this->transfer($backup['path'], $trigger, $userId);
                $say('✔ Übertragen und Prüfsumme auf dem Backup-Server bestätigt');
            } elseif ($transfer) {
                $warnings[] = 'Zweitserver ist nicht konfiguriert (BACKUP_REMOTE_ENABLED=false) – Backup liegt nur lokal.';
                $say('⚠ ' . end($warnings));
            }

            // 3. Optional: Wiederherstellungstest mit genau diesem Backup – bevor es lokal gelöscht wird
            if ($restoreTest) {
                $say('Wiederherstellungstest in die isolierte Test-Datenbank …');
                $code = Artisan::call('backup:restore-test', ['file' => $backup['path'], '--drop' => true, '--trigger' => $trigger]);
                foreach (array_filter(explode("\n", trim(Artisan::output()))) as $line) {
                    $say("  {$line}");
                }
                if ($code !== 0) {
                    throw new BackupException('Wiederherstellungstest fehlgeschlagen (siehe oben). Das Backup selbst wurde erstellt und übertragen.', BackupException::RESTORE);
                }
                $say('✔ Wiederherstellungstest erfolgreich');
            }

            // 4. Aufräumen – nur bei echten Backup-Läufen. Eine Download-Kopie soll das aufbewahrte
            //    nächtliche Backup nicht verdrängen.
            $pruned = $transfer ? $this->pruneLocal() : [];
            if ($pruned) {
                $say((RemoteBackupStore::enabled() ? 'Lokal gelöscht (sicher auf dem Zweitserver): ' : 'Lokal gelöscht (Aufbewahrung): ') . implode(', ', $pruned));
            }
            if ($transfer && RemoteBackupStore::enabled() && config('backup.remote.prune')) {
                $result = $this->remote->prune(RetentionPolicy::fromConfig());
                if ($result['delete']) {
                    $say('Remote gelöscht (Aufbewahrung): ' . implode(', ', $result['delete']));
                }
            }

            return ['ok' => true, 'code' => 0, 'file' => $file, 'message' => 'Backup erfolgreich.', 'warnings' => $warnings];
        } catch (Throwable $e) {
            $code = $e instanceof BackupException ? $e->getCode() : BackupException::GENERAL;
            $message = $e->getMessage();

            Log::channel(config('backup.log_channel'))->error("Backup-Lauf fehlgeschlagen: {$message}", ['code' => $code, 'file' => $file]);
            if ($run && $run->status === 'running') {
                $run->finish('failed', $message, ['file' => $file]);
            }
            if (RemoteBackupStore::enabled()) {
                $this->remote->appendRemoteLog('FAILED ' . ($file ?? '-') . ' ' . str_replace("\n", ' ', $message));
            }

            return ['ok' => false, 'code' => $code, 'file' => $file, 'message' => $message, 'warnings' => $warnings];
        } finally {
            $this->releaseLock($lock);
        }
    }

    public function transfer(string $archive, string $trigger = 'cli', ?int $userId = null): array
    {
        $run = BackupRun::begin('transfer', $trigger, $userId, basename($archive));

        try {
            $result = $this->remote->upload($archive);
            $run?->finish('success', $result['uploaded'] ? 'Übertragen und geprüft.' : 'War bereits vollständig vorhanden.', [
                'sha256' => $result['sha256'], 'size' => filesize($archive),
            ]);

            return $result;
        } catch (Throwable $e) {
            $run?->finish('failed', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Behält lokal die neuesten N Backups (auch 0). Gelöscht wird nur, was bestätigt auf dem Zweitserver liegt –
     * ohne Zweitserver bleibt immer mindestens eines erhalten.
     *
     * @return array<int, string>
     */
    public function pruneLocal(): array
    {
        $keep = max(RemoteBackupStore::enabled() ? 0 : 1, (int) config('backup.retention.local.keep_latest'));
        $deleted = [];
        $downloads = self::downloadOnlyFiles()->flip();
        [$downloadCopies, $backups] = BackupFiles::list()->partition(fn(array $backup) => isset($downloads[$backup['name']]));

        // Download-Kopien aus der Weboberfläche: beim nächsten Lauf weg, zählen nicht zur Aufbewahrung
        foreach ($downloadCopies as $backup) {
            if ($backup['created_at'] && $backup['created_at']->lt(now()->subHours(self::DOWNLOAD_TTL_HOURS))) {
                BackupFiles::delete($backup['path']);
                $deleted[] = $backup['name'];
            }
        }

        foreach ($backups->values()->slice($keep) as $backup) {
            if (RemoteBackupStore::enabled() && ! $this->confirmedRemote($backup['name'])) {
                continue;
            }

            BackupFiles::delete($backup['path']);
            $deleted[] = $backup['name'];
        }

        if ($deleted) {
            Log::channel(config('backup.log_channel'))->info('Lokale Backups gelöscht', ['deleted' => $deleted]);
        }

        return $deleted;
    }

    /**
     * Dateinamen der Backups, die über die Weboberfläche nur zum Herunterladen erstellt wurden.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public static function downloadOnlyFiles(): \Illuminate\Support\Collection
    {
        try {
            return BackupRun::where('action', 'create')->whereNotNull('file')->get(['file', 'details'])
                ->filter(fn(BackupRun $run) => (bool) ($run->details['download_only'] ?? false))
                ->pluck('file')
                ->values();
        } catch (Throwable) {
            return collect();
        }
    }

    private function confirmedRemote(string $name): bool
    {
        try {
            return BackupRun::where('action', 'transfer')->where('file', $name)->where('status', 'success')->exists();
        } catch (Throwable) {
            // Ohne Statusdatenbank lieber nichts löschen
            return false;
        }
    }

    /**
     * Dateisperre statt Cache-Lock: funktioniert auch, wenn die Datenbank ausfällt, und gilt für Cron, CLI und Queue.
     *
     * @return resource|null
     */
    private function acquireLock()
    {
        $handle = fopen(BackupFiles::directory() . DIRECTORY_SEPARATOR . '.backup.lock', 'c');

        if ($handle === false) {
            return null;
        }
        if (! flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return null;
        }

        ftruncate($handle, 0);
        fwrite($handle, (string) getmypid());

        return $handle;
    }

    private function releaseLock($handle): void
    {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
