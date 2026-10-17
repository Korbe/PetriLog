<?php

namespace App\Services\Backup;

/**
 * Sperrt die Website während einer produktiven Wiederherstellung über eine Datei im Backup-Ordner.
 *
 * Bewusst nicht `php artisan down`: Das würde auch den Queue-Worker anhalten, der die Wiederherstellung aus der
 * Weboberfläche ausführt. Die Datei liegt außerhalb der Datenbank, weil diese gerade ersetzt wird.
 */
class RestoreMaintenance
{
    // Ohne BackupFiles::directory(), damit die Prüfung bei jeder Anfrage keinen Ordner anlegt
    public static function file(): string
    {
        return rtrim(config('backup.local_path'), '/\\') . DIRECTORY_SEPARATOR . '.restore-in-progress.json';
    }

    public static function enable(string $backup): void
    {
        BackupFiles::directory();
        file_put_contents(self::file(), json_encode([
            'backup' => $backup,
            'started_at' => now()->toIso8601String(),
        ]));
    }

    public static function disable(): void
    {
        @unlink(self::file());
    }

    public static function active(): ?array
    {
        $file = self::file();

        if (! is_file($file)) {
            return null;
        }

        // Sicherheitsnetz: Nach 3 Stunden gilt eine Sperre als verwaist (abgestürzter Prozess)
        if (filemtime($file) < time() - 3 * 3600) {
            return null;
        }

        return json_decode((string) file_get_contents($file), true) ?: ['backup' => null];
    }
}
