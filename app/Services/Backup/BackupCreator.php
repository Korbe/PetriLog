<?php

namespace App\Services\Backup;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;
use ZipArchive;

/**
 * Erstellt ein vollständiges, konsistentes Backup:
 *
 *   {name}.zip (AES-256)          {name}.zip.sha256
 *   ├─ manifest.json              Tabellen + Zeilenzahlen, Datei-Prüfsummen, Migrationsstand
 *   ├─ database.sql               mysqldump --single-transaction (Schema, Daten, Indizes, Constraints, AUTO_INCREMENT)
 *   └─ storage/app/public/…       Fotos & Bilder (Pfade wie auf dem Server)
 *
 * Die Datei entsteht als *.part und wird erst nach erfolgreicher Prüfung umbenannt –
 * ein abgebrochenes oder beschädigtes Backup kann so nie als fertig gelten.
 */
class BackupCreator
{
    public const FORMAT_VERSION = 1;

    // Bereits komprimierte Formate nicht erneut komprimieren (spart Zeit, kaum Platzverlust)
    private const STORE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'heic', 'mp4', 'mov', 'zip', 'gz', 'pdf'];

    public function __construct(private BackupVerifier $verifier)
    {
    }

    /**
     * @return array{name: string, path: string, size: int, sha256: string, manifest: array, warnings: array}
     */
    public function create(): array
    {
        $this->assertEncryptionConfigured();

        $name = BackupFiles::newName();
        $final = BackupFiles::path($name);
        $partial = "{$final}.part";
        $work = BackupFiles::directory() . DIRECTORY_SEPARATOR . '.work-' . pathinfo($name, PATHINFO_FILENAME);

        File::ensureDirectoryExists($work, 0700);
        $this->log('info', "Backup {$name} gestartet");

        try {
            $this->assertDiskSpace();

            $dumpFile = $work . DIRECTORY_SEPARATOR . 'database.sql';
            $database = $this->dumpDatabase($dumpFile);
            $files = $this->collectStorageFiles();
            $warnings = $this->checkMediaFiles($files);

            $manifest = [
                'format' => self::FORMAT_VERSION,
                'app' => config('app.name'),
                'environment' => app()->environment(),
                'revision' => $this->revision(),
                'created_at' => CarbonImmutable::now('UTC')->toIso8601String(),
                'encrypted' => $this->password() !== null,
                'database' => $database,
                'files' => [
                    'count' => count($files),
                    'size' => array_sum(array_column($files, 'size')),
                    'items' => $files,
                ],
                'warnings' => $warnings,
            ];

            $this->writeArchive($partial, $manifest, $dumpFile, $files);

            // Vor dem Freigeben vollständig prüfen: jede Datei entpacken und gegen das Manifest hashen
            $check = $this->verifier->verify($partial, requireChecksumFile: false);
            if (! $check['ok']) {
                throw new BackupException('Backup-Prüfung fehlgeschlagen: ' . implode('; ', $check['errors']), BackupException::VERIFY);
            }

            $sha256 = BackupFiles::sha256($partial);
            if (! @rename($partial, $final)) {
                throw new BackupException("Backup konnte nicht nach {$final} verschoben werden.", BackupException::ARCHIVE);
            }
            @chmod($final, 0600);
            BackupFiles::writeChecksumFile($final, $sha256);

            $size = filesize($final);
            $this->log('info', "Backup {$name} erstellt", [
                'size' => $size, 'sha256' => $sha256, 'tables' => count($database['tables']), 'files' => count($files),
                'warnings' => count($warnings),
            ]);

            return ['name' => $name, 'path' => $final, 'size' => $size, 'sha256' => $sha256, 'manifest' => $manifest, 'warnings' => $warnings];
        } catch (Throwable $e) {
            @unlink($partial);
            $this->log('error', "Backup {$name} fehlgeschlagen: {$e->getMessage()}");
            throw $e instanceof BackupException ? $e : new BackupException($e->getMessage(), BackupException::GENERAL, $e);
        } finally {
            File::deleteDirectory($work);
        }
    }

    private function dumpDatabase(string $target): array
    {
        $connection = config('backup.database.connection');
        $databaseName = config("database.connections.{$connection}.database");
        $client = MysqlClient::forConnection(
            $connection,
            config('backup.database.username'),
            config('backup.database.password'),
        );

        $binary = MysqlClient::dumpBinary();
        $common = [
            '--skip-lock-tables',
            '--quick',
            '--hex-blob',
            '--no-tablespaces',          // kein PROCESS-Recht nötig
            '--default-character-set=utf8mb4',
            '--tz-utc',
        ];

        // GTID-Anweisungen würden den Import in eine andere Instanz verhindern (nur MySQL-Client kennt die Option)
        $version = Process::run([$binary, '--version']);
        if ($version->successful() && ! str_contains(strtolower($version->output()), 'mariadb')) {
            $common[] = '--set-gtid-purged=OFF';
        }

        // 1. Struktur ALLER Tabellen: Spalten, Primärschlüssel, Indizes, Fremdschlüssel, Trigger, AUTO_INCREMENT
        $schema = [...$common, '--no-data', '--triggers', '--add-drop-table', '--create-options'];
        if (config('backup.database.include_routines')) {
            $schema[] = '--routines';
        }
        if (config('backup.database.include_events')) {
            $schema[] = '--events';
        }

        // 2. Daten in EINEM konsistenten Schnappschuss – ohne kurzlebige Tabellen (Sitzungen, Cache, Pulse)
        $data = [...$common, '--single-transaction', '--no-create-info', '--skip-triggers', '--skip-extended-insert'];
        foreach (config('backup.database.structure_only_tables', []) as $table) {
            $data[] = "--ignore-table={$databaseName}.{$table}";
        }

        $schemaFile = "{$target}.schema";
        $dataFile = "{$target}.data";

        try {
            foreach ([[$schema, $schemaFile], [$data, $dataFile]] as [$arguments, $file]) {
                $result = $client->run($binary, [...$arguments, '--result-file=' . $file, $databaseName], config('backup.database.timeout'));

                if (! $result->successful()) {
                    throw new BackupException('mysqldump fehlgeschlagen: ' . $client->sanitize($result->errorOutput()), BackupException::DATABASE);
                }
            }

            // Struktur vor Daten in eine Datei – Fremdschlüsselprüfungen schaltet der Dump selbst ab
            $out = fopen($target, 'wb');
            foreach ([$schemaFile, $dataFile] as $part) {
                $in = fopen($part, 'rb');
                stream_copy_to_stream($in, $out);
                fclose($in);
            }
            fclose($out);
        } finally {
            @unlink($schemaFile);
            @unlink($dataFile);
        }

        return $this->analyzeDump($target, $databaseName, $client);
    }

    /**
     * Liest Tabellen und Zeilenzahlen direkt aus dem Dump – damit beschreibt das Manifest exakt den Inhalt
     * des Schnappschusses, auch wenn die App währenddessen weiterläuft.
     */
    private function analyzeDump(string $file, string $databaseName, MysqlClient $client): array
    {
        $handle = fopen($file, 'rb');
        $tables = [];
        $completed = 0;

        while (($line = fgets($handle)) !== false) {
            if (str_starts_with($line, 'INSERT INTO `')) {
                $table = substr($line, 13, strpos($line, '`', 13) - 13);
                $tables[$table] = ($tables[$table] ?? 0) + 1;
            } elseif (str_starts_with($line, 'CREATE TABLE `')) {
                $table = substr($line, 14, strpos($line, '`', 14) - 14);
                $tables[$table] ??= 0;
            } elseif (str_starts_with($line, '-- Dump completed')) {
                $completed++;
            }
        }
        fclose($handle);

        // Struktur- und Datenteil müssen je mit ihrer Abschlusszeile enden, sonst wurde ein Dump abgebrochen
        if ($completed < 2) {
            throw new BackupException('Datenbank-Dump ist unvollständig (fehlende Abschlusszeile).', BackupException::DATABASE);
        }
        if (! $tables) {
            throw new BackupException('Datenbank-Dump enthält keine Tabellen.', BackupException::DATABASE);
        }

        ksort($tables);

        return [
            'driver' => config('database.connections.' . config('backup.database.connection') . '.driver'),
            'server_version' => $this->safeVersion($client),
            'name' => $databaseName,
            'charset' => config('database.connections.' . config('backup.database.connection') . '.charset', 'utf8mb4'),
            'collation' => config('database.connections.' . config('backup.database.connection') . '.collation', 'utf8mb4_unicode_ci'),
            'file' => 'database.sql',
            'size' => filesize($file),
            'sha256' => BackupFiles::sha256($file),
            'tables' => $tables,
            'structure_only' => array_values(config('backup.database.structure_only_tables', [])),
            'migrations' => $this->migrations(),
        ];
    }

    /**
     * @return array<int, array{path: string, size: int, sha256: string}>
     */
    private function collectStorageFiles(): array
    {
        $files = [];
        $excludes = config('backup.storage_exclude', []);
        $backupDir = realpath(BackupFiles::directory());

        foreach (config('backup.storage_paths', []) as $relativeRoot) {
            $root = storage_path($relativeRoot);
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS));

            foreach ($iterator as $file) {
                /** @var \SplFileInfo $file */
                if (! $file->isFile() || $file->isLink()) {
                    continue;
                }

                $real = $file->getRealPath();
                // Niemals Backups im Backup (falls der Backup-Ordner in einem gesicherten Pfad liegt)
                if ($backupDir && str_starts_with($real, $backupDir)) {
                    continue;
                }

                $inside = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
                if ($this->excluded($inside, $excludes)) {
                    continue;
                }

                $files[] = [
                    'path' => 'storage/' . trim(str_replace('\\', '/', $relativeRoot), '/') . '/' . $inside,
                    'size' => $file->getSize(),
                    'sha256' => BackupFiles::sha256($real),
                ];
            }
        }

        usort($files, fn($a, $b) => strcmp($a['path'], $b['path']));

        return $files;
    }

    /**
     * Fängt Bilder ab, die in der Datenbank stehen, aber im Storage fehlen (würden nach einem Restore fehlen).
     */
    private function checkMediaFiles(array $files): array
    {
        try {
            $paths = array_flip(array_column($files, 'path'));
            $lowercase = array_flip(array_map('mb_strtolower', array_column($files, 'path')));
            $missing = [];
            $caseMismatch = [];

            Media::query()->select(['id', 'disk', 'file_name', 'model_type', 'model_id'])->chunkById(500, function ($media) use ($paths, $lowercase, &$missing, &$caseMismatch) {
                foreach ($media as $item) {
                    $root = config("filesystems.disks.{$item->disk}.root");
                    $relativeRoot = $root ? ltrim(str_replace('\\', '/', substr($root, strlen(storage_path()))), '/') : null;

                    if (! $relativeRoot) {
                        continue;
                    }

                    $expected = "storage/{$relativeRoot}/" . str_replace('\\', '/', $item->getPathRelativeToRoot());
                    if (isset($paths[$expected])) {
                        continue;
                    }
                    // Unter Windows unauffällig, auf Linux-Servern aber nicht auffindbar
                    if (isset($lowercase[mb_strtolower($expected)])) {
                        $caseMismatch[] = $expected;
                    } else {
                        $missing[] = $expected;
                    }
                }
            });

            $warnings = [];
            if ($missing) {
                $warnings[] = 'Bilddateien fehlen im Storage (' . count($missing) . '): ' . implode(', ', array_slice($missing, 0, 10));
            }
            if ($caseMismatch) {
                $warnings[] = 'Groß-/Kleinschreibung von Bilddateien weicht von der Datenbank ab – auf Linux nicht auffindbar ('
                    . count($caseMismatch) . '): ' . implode(', ', array_slice($caseMismatch, 0, 10));
            }

            return $warnings;
        } catch (Throwable $e) {
            return ["Bildprüfung übersprungen: {$e->getMessage()}"];
        }
    }

    private function writeArchive(string $path, array $manifest, string $dumpFile, array $files): void
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new BackupException("ZIP {$path} konnte nicht angelegt werden.", BackupException::ARCHIVE);
        }

        $password = $this->password();
        $add = function (string $entry, ?string $source, ?string $contents = null) use ($zip, $password) {
            $ok = $source !== null ? $zip->addFile($source, $entry) : $zip->addFromString($entry, $contents);
            if (! $ok) {
                throw new BackupException("{$entry} konnte nicht ins Backup geschrieben werden.", BackupException::ARCHIVE);
            }
            if (in_array(strtolower(pathinfo($entry, PATHINFO_EXTENSION)), self::STORE_EXTENSIONS, true)) {
                $zip->setCompressionName($entry, ZipArchive::CM_STORE);
            }
            if ($password !== null && ! $zip->setEncryptionName($entry, ZipArchive::EM_AES_256, $password)) {
                throw new BackupException("{$entry} konnte nicht verschlüsselt werden.", BackupException::ARCHIVE);
            }
        };

        $add('manifest.json', null, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $add('database.sql', $dumpFile);

        foreach ($files as $file) {
            $add($file['path'], storage_path(substr($file['path'], strlen('storage/'))));
        }

        if (! $zip->close()) {
            throw new BackupException('ZIP konnte nicht abgeschlossen werden: ' . $zip->getStatusString(), BackupException::ARCHIVE);
        }
    }

    private function assertEncryptionConfigured(): void
    {
        if ($this->password() === null && config('backup.require_encryption')) {
            throw new BackupException('BACKUP_ENCRYPTION_PASSWORD fehlt – unverschlüsselte Backups sind hier nicht erlaubt.', BackupException::CONFIG);
        }
        if ($this->password() !== null && ! defined('ZipArchive::EM_AES_256')) {
            throw new BackupException('Die PHP-Zip-Erweiterung unterstützt keine AES-256-Verschlüsselung.', BackupException::CONFIG);
        }
    }

    private function assertDiskSpace(): void
    {
        try {
            $dbSize = (int) DB::connection(config('backup.database.connection'))
                ->selectOne('SELECT COALESCE(SUM(data_length + index_length), 0) AS size FROM information_schema.tables WHERE table_schema = DATABASE()')
                ->size;
        } catch (Throwable) {
            $dbSize = 0;
        }

        $storageSize = 0;
        foreach (config('backup.storage_paths', []) as $relative) {
            if (is_dir($root = storage_path($relative))) {
                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                    $storageSize += $file->isFile() ? $file->getSize() : 0;
                }
            }
        }

        // Dump (unkomprimiert) + Archiv – großzügig geschätzt
        $needed = (int) (($dbSize * 2 + $storageSize) * 1.2);
        $free = @disk_free_space(BackupFiles::directory());

        if ($free !== false && $free < $needed) {
            throw new BackupException(sprintf('Zu wenig Speicherplatz: %d MB frei, ca. %d MB benötigt.', $free / 1048576, $needed / 1048576), BackupException::ARCHIVE);
        }
    }

    private function migrations(): array
    {
        try {
            return DB::connection(config('backup.database.connection'))->table('migrations')->orderBy('id')->pluck('migration')->all();
        } catch (Throwable) {
            return [];
        }
    }

    private function revision(): ?string
    {
        foreach ([base_path('REVISION'), base_path('.git/HEAD')] as $file) {
            if (! is_file($file)) {
                continue;
            }
            $content = trim((string) file_get_contents($file));
            if (str_starts_with($content, 'ref: ') && is_file($ref = base_path('.git/' . substr($content, 5)))) {
                $content = trim((string) file_get_contents($ref));
            }

            return substr($content, 0, 40) ?: null;
        }

        return null;
    }

    private function safeVersion(MysqlClient $client): ?string
    {
        try {
            return $client->version();
        } catch (Throwable) {
            return null;
        }
    }

    private function password(): ?string
    {
        $password = config('backup.encryption_password');

        return is_string($password) && $password !== '' ? $password : null;
    }

    private function excluded(string $relative, array $excludes): bool
    {
        foreach ($excludes as $pattern) {
            if (str_ends_with($pattern, '/') ? str_starts_with($relative, $pattern) : ($relative === $pattern || basename($relative) === $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel(config('backup.log_channel'))->{$level}($message, $context);
    }
}
