<?php

namespace App\Console\Commands;

use ZipArchive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DownloadAndRestoreBackups extends Command
{
    // php artisan backup:restore
    protected $signature = 'backup:restore';
    protected $description = 'Download latest Petrilog backup from Server A and restore DB and media locally';

    protected string $localBackupPath = 'backup'; // storage/backup
    protected string $localMediaPath = 'app/public'; // storage/app/public

    public function handle()
    {
        $this->info('🔌 Verbinde mit Petrilog SFTP (Server A)...');

        $sftp = Storage::disk('sftp');

        /**
         * 1️⃣ Letzten Datumsordner finden (YYYY-MM-DD)
         */
        $directories = collect($sftp->directories())
            ->filter(fn($dir) => preg_match('/\d{4}-\d{2}-\d{2}$/', $dir))
            ->sortDesc()
            ->values();

        if ($directories->isEmpty()) {
            $this->error('❌ Keine Backup-Ordner auf dem Server gefunden.');
            return Command::FAILURE;
        }

        $latestDir = $directories->first();
        $this->info("📦 Letztes Backup gefunden: {$latestDir}");

        /**
         * 2️⃣ Dateien prüfen
         */
        $requiredFiles = ['structure.sql', 'data.sql', 'media.zip'];

        foreach ($requiredFiles as $file) {
            if (! $sftp->exists("$latestDir/$file")) {
                $this->error("❌ Datei fehlt: {$latestDir}/{$file}");
                return Command::FAILURE;
            }
        }

        /**
         * 3️⃣ Lokales Backup-Verzeichnis erstellen
         */
        $localBasePath = storage_path($this->localBackupPath);

        if (! is_dir($localBasePath)) {
            mkdir($localBasePath, 0755, true);
        }

        /**
         * 4️⃣ Dateien herunterladen
         */
        foreach ($requiredFiles as $file) {
            $this->info("⬇️ Lade {$file} herunter...");
            $content = $sftp->get("$latestDir/$file");
            file_put_contents("{$localBasePath}/{$file}", $content);
        }

        /**
         * 5️⃣ Datenbank wiederherstellen
         */
        $this->info('🗄️ Stelle Datenbank wieder her...');

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Pfad zum MySQL-Client aus .env
        $mysqlPath = env('MYSQL_PATH', 'mysql'); // Fallback auf "mysql" falls nicht gesetzt

        foreach (['structure.sql', 'data.sql'] as $sqlFile) {
            $path = "{$localBasePath}/{$sqlFile}";
            $this->info("▶ Importiere {$sqlFile}...");

            $command = sprintf(
                '"%s" -u%s -p%s %s < "%s"',
                $mysqlPath,
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                $path
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                $this->error("❌ Fehler beim Import von {$sqlFile}");
                return Command::FAILURE;
            }
        }

        /**
         * 6️⃣ Media wiederherstellen
         */
        $this->info('🖼️ Stelle Media-Dateien wieder her...');

        $mediaZip = "{$localBasePath}/media.zip";
        $mediaTarget = storage_path($this->localMediaPath);

        if (is_dir($mediaTarget)) {
            File::deleteDirectory($mediaTarget);
        }

        mkdir($mediaTarget, 0755, true);

        $zip = new ZipArchive();

        if ($zip->open($mediaZip) === true) {
            $zip->extractTo($mediaTarget);
            $zip->close();
        } else {
            $this->error('❌ media.zip konnte nicht geöffnet werden');
            return Command::FAILURE;
        }

        $this->info('✅ Restore abgeschlossen!');
        $this->line("📁 Quelle: {$latestDir}");
        $this->line("📁 Lokal: storage/{$this->localBackupPath}");

        return Command::SUCCESS;
    }
}
