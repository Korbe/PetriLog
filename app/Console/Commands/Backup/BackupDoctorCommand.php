<?php

namespace App\Console\Commands\Backup;

use App\Services\Backup\BackupFiles;
use App\Services\Backup\MysqlClient;
use App\Services\Backup\RemoteBackupStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Throwable;
use ZipArchive;

class BackupDoctorCommand extends Command
{
    protected $signature = 'backup:doctor';

    protected $description = 'Prüft alle Voraussetzungen für Backups (Werkzeuge, Rechte, Verschlüsselung, Zweitserver)';

    public function handle(RemoteBackupStore $remote): int
    {
        $rows = [];
        $failed = false;
        $check = function (string $label, callable $test) use (&$rows, &$failed) {
            try {
                [$ok, $info] = $test();
            } catch (Throwable $e) {
                [$ok, $info] = [false, $e->getMessage()];
            }
            $rows[] = [$ok ? '✔' : '✘', $label, $info];
            $failed = $failed || ! $ok;
        };

        $check('mysqldump', function () {
            $result = Process::run([MysqlClient::dumpBinary(), '--version']);
            return [$result->successful(), trim($result->output()) ?: trim($result->errorOutput())];
        });
        $check('mysql-Client', function () {
            $result = Process::run([MysqlClient::clientBinary(), '--version']);
            return [$result->successful(), trim($result->output()) ?: trim($result->errorOutput())];
        });
        $check('Datenbank erreichbar', function () {
            $client = MysqlClient::forConnection(config('backup.database.connection'), config('backup.database.username'), config('backup.database.password'));
            return [true, $client->version()];
        });
        $check('ZIP-Erweiterung', fn() => [class_exists(ZipArchive::class), class_exists(ZipArchive::class) ? 'libzip ' . phpversion('zip') : 'fehlt']);
        $check('Verschlüsselung', function () {
            $password = config('backup.encryption_password');
            if (! $password) {
                return [! config('backup.require_encryption'), 'BACKUP_ENCRYPTION_PASSWORD nicht gesetzt'];
            }
            return [defined('ZipArchive::EM_AES_256'), strlen($password) >= 20 ? 'AES-256 aktiv' : 'AES-256 aktiv – Passwort sollte ≥ 20 Zeichen haben'];
        });
        $check('Backup-Ordner', function () {
            $dir = BackupFiles::directory();
            $writable = is_writable($dir);
            $perms = PHP_OS_FAMILY === 'Windows' ? '' : ' (Rechte ' . substr(sprintf('%o', fileperms($dir)), -4) . ')';
            $public = str_starts_with(realpath($dir), realpath(public_path()));
            return [$writable && ! $public, $dir . $perms . ($public ? ' – liegt öffentlich erreichbar!' : '')];
        });
        $check('Freier Speicher', function () {
            $free = disk_free_space(BackupFiles::directory());
            return [$free > 1024 ** 3, number_format($free / 1024 ** 3, 1, ',', '.') . ' GB frei'];
        });
        $check('Zweitserver', function () use ($remote) {
            if (! RemoteBackupStore::enabled()) {
                return [true, 'nicht aktiviert (BACKUP_REMOTE_ENABLED=false)'];
            }
            if (! config('backup.remote.host_fingerprint')) {
                return [false, 'BACKUP_REMOTE_HOST_FINGERPRINT fehlt (Schutz vor Man-in-the-Middle)'];
            }
            $count = count($remote->list());
            $probe = '.doctor-' . uniqid();
            $remote->disk()->put($probe, 'ok');
            $remote->disk()->delete($probe);
            return [true, config('backup.remote.username') . '@' . config('backup.remote.host') . ':' . config('backup.remote.root') . " – schreibbar, {$count} Backups"];
        });

        $this->table(['', 'Prüfung', 'Ergebnis'], $rows);

        return $failed ? 2 : self::SUCCESS;
    }
}
