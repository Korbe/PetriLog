<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;
use ZipArchive;

/**
 * Stellt ein geprüftes Backup wieder her und kontrolliert das Ergebnis:
 * Tabellen, exakte Zeilenzahlen, Fremdschlüssel, Bild-Verknüpfungen, AUTO_INCREMENT, Dateien und Migrationsstand.
 *
 * Standard ist eine isolierte Test-Datenbank – die produktive Datenbank wird nur mit ausdrücklicher Bestätigung
 * (BackupRestorer::PRODUCTION) überschrieben.
 */
class BackupRestorer
{
    public const TEST = 'test';
    public const PRODUCTION = 'production';

    private const CHECK_CONNECTION = 'backup_restore_check';

    public function __construct(private BackupVerifier $verifier)
    {
    }

    /**
     * @param array{database?: string, files_target?: ?string, restore_files?: bool} $options
     * @return array{ok: bool, status: string, database: string, errors: array, warnings: array, checks: array, files: array}
     */
    public function restore(string $archive, string $mode = self::TEST, array $options = []): array
    {
        $connection = config('backup.database.connection');
        $productionDatabase = config("database.connections.{$connection}.database");
        $target = $options['database'] ?? ($mode === self::PRODUCTION ? $productionDatabase : config('backup.restore.test_database'));

        $this->assertSafeTarget($target, $productionDatabase, $mode);

        // 1. Backup vollständig prüfen – ein beschädigtes Backup wird nie eingespielt
        $check = $this->verifier->verify($archive, requireChecksumFile: false);
        if (! $check['ok']) {
            throw new BackupException('Backup ist ungültig: ' . implode('; ', $check['errors']), BackupException::VERIFY);
        }
        $manifest = $check['manifest'];

        $this->log('info', "Wiederherstellung von " . basename($archive) . " in „{$target}“ ({$mode}) gestartet");

        $client = $mode === self::TEST && config('backup.restore.username')
            ? MysqlClient::forConnection($connection, config('backup.restore.username'), config('backup.restore.password'))
            : MysqlClient::forConnection($connection);

        // 2. Ziel vorbereiten: Test-Datenbank immer frisch anlegen
        if ($mode === self::TEST) {
            $this->recreateDatabase($client, $target, $manifest['database']);
        }

        // 3. Dump einspielen
        $this->importDump($archive, $client, $target);

        // 4. Dateien
        $restoreFiles = $options['restore_files'] ?? true;
        $filesTarget = $options['files_target'] ?? ($mode === self::PRODUCTION ? storage_path() : null);
        $files = $restoreFiles && $filesTarget ? $this->restoreFiles($archive, $manifest, $filesTarget) : ['written' => 0, 'skipped' => 0, 'target' => null];

        // 5. Ergebnis kontrollieren
        $result = $this->checkDatabase($target, $manifest, $files['target']);
        $result['files'] = $files;
        $result['database'] = $target;
        $result['manifest'] = [
            'created_at' => $manifest['created_at'] ?? null,
            'revision' => $manifest['revision'] ?? null,
            'tables' => count($manifest['database']['tables']),
            'rows' => array_sum($manifest['database']['tables']),
            'files' => $manifest['files']['count'] ?? 0,
        ];

        $this->log($result['ok'] ? 'info' : 'error', "Wiederherstellung in „{$target}“ abgeschlossen: {$result['status']}", [
            'errors' => $result['errors'], 'warnings' => $result['warnings'],
        ]);

        return $result;
    }

    /**
     * Entfernt eine Test-Datenbank nach dem Restore-Test wieder.
     */
    public function dropTestDatabase(string $database): void
    {
        $this->assertSafeTarget($database, config('database.connections.' . config('backup.database.connection') . '.database'), self::TEST);

        $client = config('backup.restore.username')
            ? MysqlClient::forConnection(config('backup.database.connection'), config('backup.restore.username'), config('backup.restore.password'))
            : MysqlClient::forConnection(config('backup.database.connection'));

        $this->sql($client, 'DROP DATABASE IF EXISTS ' . $this->quoteIdentifier($database));
    }

    private function assertSafeTarget(string $target, string $productionDatabase, string $mode): void
    {
        if (! preg_match('/^[A-Za-z0-9_]{1,64}$/', $target)) {
            throw new BackupException("Ungültiger Datenbankname: {$target}", BackupException::CONFIG);
        }

        if ($mode === self::TEST && strcasecmp($target, $productionDatabase) === 0) {
            throw new BackupException('Ein Wiederherstellungstest darf nicht in die produktive Datenbank schreiben.', BackupException::RESTORE);
        }
    }

    private function recreateDatabase(MysqlClient $client, string $database, array $meta): void
    {
        $charset = preg_replace('/[^a-z0-9_]/i', '', $meta['charset'] ?? 'utf8mb4');
        $collation = preg_replace('/[^a-z0-9_]/i', '', $meta['collation'] ?? 'utf8mb4_unicode_ci');
        $name = $this->quoteIdentifier($database);

        $this->sql($client, "DROP DATABASE IF EXISTS {$name}; CREATE DATABASE {$name} CHARACTER SET {$charset} COLLATE {$collation};");
    }

    private function importDump(string $archive, MysqlClient $client, string $database): void
    {
        $tempSql = tempnam(sys_get_temp_dir(), 'petrilog-restore');
        @chmod($tempSql, 0600);

        try {
            $zip = $this->openArchive($archive);
            $source = $zip->getStream('database.sql');
            $target = fopen($tempSql, 'wb');
            stream_copy_to_stream($source, $target);
            fclose($source);
            fclose($target);
            $zip->close();

            $input = fopen($tempSql, 'rb');
            $result = $client->run(MysqlClient::clientBinary(), ['--default-character-set=utf8mb4', $database], (int) config('backup.database.timeout'), $input);
            fclose($input);

            if (! $result->successful()) {
                throw new BackupException('Import fehlgeschlagen: ' . $client->sanitize($result->errorOutput()), BackupException::RESTORE);
            }
        } finally {
            @unlink($tempSql);
        }
    }

    /**
     * Schreibt die Storage-Dateien an ihre ursprünglichen Pfade. Identische Dateien werden übersprungen.
     */
    private function restoreFiles(string $archive, array $manifest, string $targetRoot): array
    {
        $targetRoot = rtrim($targetRoot, '/\\');
        File::ensureDirectoryExists($targetRoot, 0755);

        $zip = $this->openArchive($archive);
        $written = 0;
        $skipped = 0;

        try {
            foreach ($manifest['files']['items'] ?? [] as $file) {
                $relative = $this->safeRelativePath($file['path']);
                $destination = $targetRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

                // Duplikat: gleiche Datei liegt schon dort → überspringen
                if (is_file($destination) && filesize($destination) === $file['size'] && hash_equals($file['sha256'], BackupFiles::sha256($destination))) {
                    $skipped++;
                    continue;
                }

                File::ensureDirectoryExists(dirname($destination), 0755);

                $source = $zip->getStream($file['path']);
                if ($source === false) {
                    throw new BackupException("{$file['path']} konnte nicht entpackt werden.", BackupException::RESTORE);
                }

                // Erst in eine temporäre Datei, dann atomar ersetzen
                $temporary = $destination . '.restore-tmp';
                $handle = fopen($temporary, 'wb');
                stream_copy_to_stream($source, $handle);
                fclose($handle);
                fclose($source);

                if (! hash_equals($file['sha256'], BackupFiles::sha256($temporary))) {
                    @unlink($temporary);
                    throw new BackupException("{$file['path']} ist nach dem Entpacken beschädigt.", BackupException::RESTORE);
                }

                @unlink($destination);
                rename($temporary, $destination);
                $written++;
            }
        } finally {
            $zip->close();
        }

        return ['written' => $written, 'skipped' => $skipped, 'target' => $targetRoot];
    }

    /**
     * Kontrolliert die wiederhergestellte Datenbank über eine temporäre Verbindung.
     */
    private function checkDatabase(string $database, array $manifest, ?string $filesRoot): array
    {
        $errors = [];
        $warnings = $manifest['warnings'] ?? [];
        $checks = [];

        $base = config('database.connections.' . config('backup.database.connection'));
        Config::set('database.connections.' . self::CHECK_CONNECTION, [...$base, 'database' => $database]);
        DB::purge(self::CHECK_CONNECTION);
        $db = DB::connection(self::CHECK_CONNECTION);

        try {
            try {
                // Sonst liefert MySQL 8 zwischengespeicherte AUTO_INCREMENT-Werte
                $db->statement('SET SESSION information_schema_stats_expiry = 0');
            } catch (Throwable) {
                // MariaDB kennt die Variable nicht – dort sind die Werte ohnehin aktuell
            }

            // Tabellen
            $expectedTables = array_keys($manifest['database']['tables']);
            $actualTables = array_map(fn($row) => array_values((array) $row)[0], $db->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"));
            $missingTables = array_values(array_diff($expectedTables, $actualTables));
            if ($missingTables) {
                $errors[] = 'Fehlende Tabellen: ' . implode(', ', $missingTables);
            }
            $checks['tables'] = ['expected' => count($expectedTables), 'restored' => count(array_intersect($expectedTables, $actualTables))];

            // Exakte Zeilenzahlen (aus dem Dump gezählt)
            $mismatches = [];
            foreach ($manifest['database']['tables'] as $table => $rows) {
                if (in_array($table, $missingTables, true)) {
                    continue;
                }
                $count = (int) $db->table($table)->count();
                if ($count !== (int) $rows) {
                    $mismatches[] = "{$table}: {$count} statt {$rows}";
                }
            }
            if ($mismatches) {
                $errors[] = 'Zeilenzahlen weichen ab: ' . implode('; ', $mismatches);
            }
            $checks['rows'] = array_sum($manifest['database']['tables']);

            // Fremdschlüssel: verwaiste Datensätze?
            $foreignKeys = $db->select('SELECT TABLE_NAME AS child, COLUMN_NAME AS col, REFERENCED_TABLE_NAME AS parent, REFERENCED_COLUMN_NAME AS ref
                FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL', [$database]);
            $orphans = [];
            foreach ($foreignKeys as $fk) {
                $count = (int) $db->selectOne(sprintf(
                    'SELECT COUNT(*) AS c FROM %1$s c LEFT JOIN %2$s p ON c.%3$s = p.%4$s WHERE c.%3$s IS NOT NULL AND p.%4$s IS NULL',
                    $this->quoteIdentifier($fk->child), $this->quoteIdentifier($fk->parent), $this->quoteIdentifier($fk->col), $this->quoteIdentifier($fk->ref),
                ))->c;
                if ($count > 0) {
                    $orphans[] = "{$fk->child}.{$fk->col} → {$fk->parent}: {$count}";
                }
            }
            if ($orphans) {
                $errors[] = 'Verwaiste Fremdschlüssel: ' . implode('; ', $orphans);
            }
            $checks['foreign_keys'] = count($foreignKeys);

            // Polymorphe Bild-Verknüpfungen (ohne echten Fremdschlüssel)
            if (in_array('media', $actualTables, true)) {
                $mediaOrphans = [];
                foreach ($db->table('media')->select('model_type')->distinct()->pluck('model_type') as $type) {
                    $table = class_exists($type) ? (new $type())->getTable() : null;
                    if (! $table || ! in_array($table, $actualTables, true)) {
                        $mediaOrphans[] = "{$type}: Tabelle unbekannt";
                        continue;
                    }
                    $count = (int) $db->table('media')
                        ->where('model_type', $type)
                        ->whereNotExists(fn($q) => $q->from($table)->whereColumn("{$table}.id", 'media.model_id'))
                        ->count();
                    if ($count) {
                        $mediaOrphans[] = "{$type}: {$count}";
                    }
                }
                if ($mediaOrphans) {
                    $warnings[] = 'Bilder ohne zugehörigen Datensatz: ' . implode('; ', $mediaOrphans);
                }
            }

            // AUTO_INCREMENT muss über der höchsten ID liegen, sonst gäbe es Kollisionen bei neuen Datensätzen
            $autoIncrements = $db->select('SELECT TABLE_NAME AS t, AUTO_INCREMENT AS ai FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND AUTO_INCREMENT IS NOT NULL', [$database]);
            $badCounters = [];
            foreach ($autoIncrements as $row) {
                $column = $db->selectOne("SELECT COLUMN_NAME AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND EXTRA LIKE ?", [$database, $row->t, '%auto_increment%']);
                if (! $column) {
                    continue;
                }
                $max = (int) $db->table($row->t)->max($column->c);
                if ($max > 0 && (int) $row->ai <= $max) {
                    $badCounters[] = "{$row->t} (AUTO_INCREMENT {$row->ai}, höchste ID {$max})";
                }
            }
            if ($badCounters) {
                $errors[] = 'AUTO_INCREMENT zu niedrig: ' . implode('; ', $badCounters);
            }
            $checks['auto_increment'] = count($autoIncrements);

            // Bilder vorhanden?
            if ($filesRoot && in_array('media', $actualTables, true)) {
                $missingFiles = 0;
                foreach ($db->table('media')->select(['id', 'disk', 'file_name'])->cursor() as $media) {
                    $diskRoot = config("filesystems.disks.{$media->disk}.root");
                    if (! $diskRoot || ! str_starts_with($diskRoot, storage_path())) {
                        continue;
                    }
                    $relative = ltrim(substr($diskRoot, strlen(storage_path())), '/\\');
                    if (! is_file($filesRoot . DIRECTORY_SEPARATOR . $relative . DIRECTORY_SEPARATOR . $media->id . DIRECTORY_SEPARATOR . $media->file_name)) {
                        $missingFiles++;
                    }
                }
                $checks['media_files_missing'] = $missingFiles;
                if ($missingFiles) {
                    $warnings[] = "{$missingFiles} Bild(er) aus der Datenbank fehlen in den wiederhergestellten Dateien.";
                }
            }

            // Migrationsstand gegenüber dem aktuellen Code
            if (in_array('migrations', $actualTables, true)) {
                $ran = $db->table('migrations')->pluck('migration')->all();
                $available = array_map(fn($f) => pathinfo($f, PATHINFO_FILENAME), glob(database_path('migrations/*.php')));
                $pending = array_values(array_diff($available, $ran));
                $unknown = array_values(array_diff($ran, $available));
                $checks['migrations'] = ['ran' => count($ran), 'pending' => count($pending), 'unknown' => count($unknown)];
                if ($pending) {
                    $warnings[] = count($pending) . ' Migration(en) sind neuer als das Backup – nach einer echten Wiederherstellung `php artisan migrate` ausführen.';
                }
                if ($unknown) {
                    $warnings[] = count($unknown) . ' Migration(en) im Backup sind im aktuellen Code unbekannt (Backup ist neuer als der Code).';
                }
            }

            // Funktionsprobe: typische Abfragen der App gegen die wiederhergestellten Daten
            $checks['smoke'] = $this->smokeTest($db, $actualTables);
        } catch (Throwable $e) {
            $errors[] = "Kontrolle abgebrochen: {$e->getMessage()}";
        } finally {
            DB::purge(self::CHECK_CONNECTION);
        }

        $status = $errors ? 'failed' : ($warnings ? 'warning' : 'success');

        return ['ok' => ! $errors, 'status' => $status, 'errors' => $errors, 'warnings' => array_values($warnings), 'checks' => $checks];
    }

    private function smokeTest($db, array $tables): array
    {
        $results = [];

        if (in_array('users', $tables, true)) {
            $results['users'] = (int) $db->table('users')->count();
        }
        if (in_array('catcheds', $tables, true)) {
            // Fang inkl. User, Fischart und Gewässer – so wie das Dashboard ihn lädt
            $catch = $db->table('catcheds')
                ->leftJoin('users', 'users.id', '=', 'catcheds.user_id')
                ->leftJoin('fish', 'fish.id', '=', 'catcheds.fish_id')
                ->select('catcheds.id', 'users.email', 'fish.name')
                ->orderByDesc('catcheds.id')
                ->first();
            $results['latest_catch'] = $catch ? "#{$catch->id} ({$catch->name})" : 'keine Fänge';
        }
        if (in_array('fish', $tables, true)) {
            $results['fish'] = (int) $db->table('fish')->count();
        }

        return $results;
    }

    private function openArchive(string $archive): ZipArchive
    {
        $zip = new ZipArchive();
        if ($zip->open($archive, ZipArchive::RDONLY) !== true) {
            throw new BackupException("Backup {$archive} kann nicht geöffnet werden.", BackupException::RESTORE);
        }
        $this->verifier->applyPassword($zip);

        return $zip;
    }

    /**
     * Nur Pfade unterhalb von storage/ zulassen (Schutz vor „../“ in manipulierten Archiven).
     */
    private function safeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        if (! str_starts_with($path, 'storage/') || str_contains($path, '../') || str_contains($path, '/..') || str_contains($path, "\0")) {
            throw new BackupException("Unzulässiger Pfad im Backup: {$path}", BackupException::RESTORE);
        }

        return substr($path, strlen('storage/'));
    }

    private function sql(MysqlClient $client, string $statement): void
    {
        $result = $client->run(MysqlClient::clientBinary(), ['--batch', '-e', $statement], 120);

        if (! $result->successful()) {
            throw new BackupException('SQL fehlgeschlagen: ' . $client->sanitize($result->errorOutput()), BackupException::RESTORE);
        }
    }

    private function quoteIdentifier(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }

    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel(config('backup.log_channel'))->{$level}($message, $context);
    }
}
