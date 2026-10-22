<?php

namespace App\Services\Backup;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Lokaler Backup-Ordner: Pfade, Dateinamen, Auflistung. Liegt außerhalb von public/ und wird nie mitgesichert.
 */
class BackupFiles
{
    public const NAME_PATTERN = '/^(?<prefix>[a-z0-9-]+)-(?<date>\d{4}-\d{2}-\d{2})_(?<time>\d{6})\.zip$/';

    /**
     * Rechte für den Backup-Ordner und die fertigen (verschlüsselten) Backups: Besitzer und Gruppe bzw. die per ACL
     * freigegebenen Benutzer, niemand sonst. Nicht 0700/0600 – auf dem Server erstellt `deployer` die nächtlichen
     * Backups, `www-data` zeigt sie in der Weboberfläche an und erstellt Download-Kopien. Ein chmod 0700 würde die
     * ACL-Maske leeren und `www-data` aussperren („Permission denied“).
     */
    public const DIRECTORY_MODE = 0770;

    public const FILE_MODE = 0660;

    public static function directory(): string
    {
        $dir = rtrim(config('backup.local_path'), '/\\');
        File::ensureDirectoryExists($dir, self::DIRECTORY_MODE);
        @chmod($dir, self::DIRECTORY_MODE);

        return $dir;
    }

    public static function newName(?CarbonImmutable $at = null): string
    {
        // UTC, damit Sommerzeit-Umstellungen keine doppelten oder fehlenden Namen erzeugen
        $at ??= CarbonImmutable::now('UTC');

        return config('backup.prefix') . '-' . $at->utc()->format('Y-m-d_His') . '.zip';
    }

    public static function createdAt(string $name): ?CarbonImmutable
    {
        if (! preg_match(self::NAME_PATTERN, basename($name), $m)) {
            return null;
        }

        return CarbonImmutable::createFromFormat('Y-m-d His', "{$m['date']} {$m['time']}", 'UTC') ?: null;
    }

    public static function path(string $name): string
    {
        return self::directory() . DIRECTORY_SEPARATOR . self::safeName($name);
    }

    /**
     * Nur echte Backup-Dateinamen zulassen – verhindert Pfad-Manipulation (../) über Web oder CLI.
     */
    public static function safeName(string $name): string
    {
        $name = basename($name);

        if (! preg_match(self::NAME_PATTERN, $name)) {
            throw new BackupException("Ungültiger Backup-Dateiname: {$name}", BackupException::CONFIG);
        }

        return $name;
    }

    /**
     * Fertige lokale Backups, neueste zuerst (unfertige *.part-Dateien zählen nicht).
     *
     * @return Collection<int, array{name: string, path: string, size: int, created_at: CarbonImmutable, sha256: ?string}>
     */
    public static function list(): Collection
    {
        return collect(File::files(self::directory()))
            ->filter(fn($file) => preg_match(self::NAME_PATTERN, $file->getFilename()))
            ->map(fn($file) => [
                'name' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size' => $file->getSize(),
                'created_at' => self::createdAt($file->getFilename()),
                'sha256' => self::readChecksumFile($file->getPathname()),
            ])
            ->sortByDesc(fn($b) => $b['created_at'])
            ->values();
    }

    public static function latest(): ?array
    {
        return self::list()->first();
    }

    public static function resolve(?string $nameOrPath): string
    {
        if ($nameOrPath === null || $nameOrPath === '') {
            return self::latest()['path'] ?? throw new BackupException('Kein lokales Backup gefunden.', BackupException::CONFIG);
        }

        // Expliziter Pfad (z. B. von einem anderen Server heruntergeladen) ist für CLI erlaubt
        if (is_file($nameOrPath)) {
            return realpath($nameOrPath);
        }

        $path = self::path($nameOrPath);

        return is_file($path) ? $path : throw new BackupException("Backup nicht gefunden: {$nameOrPath}", BackupException::CONFIG);
    }

    public static function checksumFile(string $archive): string
    {
        return $archive . '.sha256';
    }

    /**
     * Schreibt eine Prüfsummendatei im Format von `sha256sum` (prüfbar mit `sha256sum -c`).
     */
    public static function writeChecksumFile(string $archive, string $sha256): void
    {
        $file = self::checksumFile($archive);
        file_put_contents($file, "{$sha256}  " . basename($archive) . "\n");
        @chmod($file, self::FILE_MODE);
    }

    public static function readChecksumFile(string $archive): ?string
    {
        $file = self::checksumFile($archive);

        if (! is_file($file) || ! preg_match('/^([a-f0-9]{64})\s/', (string) file_get_contents($file), $m)) {
            return null;
        }

        return $m[1];
    }

    public static function sha256(string $path): string
    {
        $hash = hash_file('sha256', $path);

        return $hash ?: throw new BackupException("Prüfsumme für {$path} konnte nicht berechnet werden.", BackupException::ARCHIVE);
    }

    public static function delete(string $archive): void
    {
        @unlink($archive);
        @unlink(self::checksumFile($archive));
    }
}
