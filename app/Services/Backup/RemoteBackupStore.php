<?php

namespace App\Services\Backup;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Überträgt Backups per SFTP (SSH-Schlüssel, Host-Fingerprint) auf einen separaten Backup-Server.
 *
 * Ablauf: Upload als *.part → SHA-256 der Datei auf dem Zielserver berechnen → erst bei Übereinstimmung
 * in den endgültigen Namen umbenennen und die .sha256-Datei ablegen. Unvollständige Uploads gelten nie als Backup.
 */
class RemoteBackupStore
{
    private const REMOTE_LOG = 'petrilog-backup.log';

    private const REMOTE_LOG_MAX_LINES = 2000;

    public function __construct(private ?Filesystem $disk = null)
    {
    }

    public static function enabled(): bool
    {
        return (bool) config('backup.remote.enabled');
    }

    public function disk(): Filesystem
    {
        if ($this->disk) {
            return $this->disk;
        }

        $config = config('backup.remote');

        foreach (['host', 'username', 'private_key'] as $key) {
            if (empty($config[$key])) {
                throw new BackupException("BACKUP_REMOTE_" . strtoupper($key === 'private_key' ? 'PRIVATE_KEY' : $key) . ' fehlt.', BackupException::CONFIG);
            }
        }
        if (! is_readable($config['private_key'])) {
            throw new BackupException('SSH-Schlüssel ist nicht lesbar: ' . $config['private_key'], BackupException::CONFIG);
        }

        return $this->disk = Storage::build([
            'driver' => 'sftp',
            'host' => $config['host'],
            'port' => $config['port'],
            'username' => $config['username'],
            'privateKey' => $config['private_key'],
            'passphrase' => $config['passphrase'] ?: null,
            'hostFingerprint' => $config['host_fingerprint'] ?: null,
            'root' => $config['root'],
            'timeout' => $config['timeout'],
            'maxTries' => 3,
            'visibility' => 'private',
            'directory_visibility' => 'private',
            'throw' => true,
        ]);
    }

    /**
     * @return array{name: string, sha256: string, uploaded: bool}
     */
    public function upload(string $archive): array
    {
        $name = basename($archive);
        $local = BackupFiles::readChecksumFile($archive) ?? BackupFiles::sha256($archive);
        $disk = $this->disk();

        // Bereits vollständig vorhanden (z. B. nach einem abgebrochenen Lauf)? Dann nur prüfen.
        if ($disk->exists($name) && hash_equals($local, $this->remoteChecksum($name))) {
            $this->log('info', "{$name} liegt bereits verifiziert auf dem Backup-Server");
            return ['name' => $name, 'sha256' => $local, 'uploaded' => false];
        }

        $partial = "{$name}.part";
        $stream = fopen($archive, 'rb');

        try {
            $disk->writeStream($partial, $stream);
        } catch (Throwable $e) {
            throw new BackupException("Upload von {$name} fehlgeschlagen: {$e->getMessage()}", BackupException::TRANSFER, $e);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        // Integritätsprüfung auf dem Zielserver
        $remote = $this->remoteChecksum($partial);
        if (! hash_equals($local, $remote)) {
            $this->safeDelete($partial);
            throw new BackupException("Prüfsumme nach Upload von {$name} weicht ab (lokal {$local}, remote {$remote}).", BackupException::TRANSFER);
        }

        try {
            if ($disk->exists($name)) {
                $disk->delete($name);
            }
            $disk->move($partial, $name);
            $disk->put("{$name}.sha256", "{$local}  {$name}\n");
        } catch (Throwable $e) {
            throw new BackupException("Abschluss des Uploads von {$name} fehlgeschlagen: {$e->getMessage()}", BackupException::TRANSFER, $e);
        }

        $this->appendRemoteLog("OK {$name} sha256={$local} size=" . filesize($archive));
        $this->log('info', "{$name} auf den Backup-Server übertragen und geprüft", ['sha256' => $local]);

        return ['name' => $name, 'sha256' => $local, 'uploaded' => true];
    }

    public function exists(string $name): bool
    {
        try {
            return $this->disk()->exists($name);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, CarbonImmutable>
     */
    public function list(): array
    {
        $backups = [];

        foreach ($this->disk()->files() as $file) {
            if (preg_match(BackupFiles::NAME_PATTERN, $file) && ($date = BackupFiles::createdAt($file))) {
                $backups[$file] = $date;
            }
        }

        return $backups;
    }

    /**
     * Aufbewahrung auf dem Backup-Server durchsetzen (nur wenn BACKUP_REMOTE_PRUNE=true).
     */
    public function prune(RetentionPolicy $policy): array
    {
        $result = $policy->apply($this->list());

        foreach ($result['delete'] as $name) {
            $this->safeDelete($name);
            $this->safeDelete("{$name}.sha256");
        }

        if ($result['delete']) {
            $this->appendRemoteLog('PRUNE ' . implode(', ', $result['delete']));
            $this->log('info', 'Alte Backups auf dem Backup-Server gelöscht', ['deleted' => $result['delete']]);
        }

        return $result;
    }

    /**
     * Fehler auch auf dem Backup-Server festhalten (best effort).
     */
    public function appendRemoteLog(string $line): void
    {
        try {
            $disk = $this->disk();
            $existing = $disk->exists(self::REMOTE_LOG) ? (string) $disk->get(self::REMOTE_LOG) : '';
            $lines = array_slice(array_filter(explode("\n", $existing)), -self::REMOTE_LOG_MAX_LINES);
            $lines[] = now('UTC')->toIso8601String() . ' ' . gethostname() . ' ' . $line;
            $disk->put(self::REMOTE_LOG, implode("\n", $lines) . "\n");
        } catch (Throwable $e) {
            $this->log('warning', "Remote-Log konnte nicht geschrieben werden: {$e->getMessage()}");
        }
    }

    private function remoteChecksum(string $path): string
    {
        try {
            return $this->disk()->checksum($path, ['checksum_algo' => 'sha256']);
        } catch (Throwable $e) {
            throw new BackupException("Prüfsumme von {$path} auf dem Backup-Server nicht ermittelbar: {$e->getMessage()}", BackupException::TRANSFER, $e);
        }
    }

    private function safeDelete(string $path): void
    {
        try {
            $this->disk()->delete($path);
        } catch (Throwable) {
            // Datei existiert nicht oder ist bereits gelöscht
        }
    }

    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel(config('backup.log_channel'))->{$level}($message, $context);
    }
}
