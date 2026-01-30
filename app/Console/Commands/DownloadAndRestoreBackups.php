<?php

namespace App\Console\Commands;

use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DownloadAndRestoreBackups extends Command
{
    //php artisan backup:restore

    protected $signature = 'backup:restore';
    protected $description = 'Download the latest backup files and restore them to local DB and media folder';

    protected $localBackupPath = 'backups';
    protected $localMediaPath = 'app/public'; // relative to storage_path

    public function handle()
    {
        $this->info('Connecting to SFTP server...');

        $sftp = Storage::disk('sftp');
        $files = $sftp->files('');

        if (empty($files)) {
            $this->warn('No files found on SFTP server.');
            return 0;
        }

        // --- SQL Backups ---
        $backupFiles = collect($files)->filter(
            fn($file) => Str::endsWith($file, '.sql') &&
                (Str::startsWith($file, 'data_') || Str::startsWith($file, 'structure_'))
        );

        $backupFiles = $backupFiles->sortByDesc(fn($file) => $sftp->lastModified($file));

        $latestData = $backupFiles->first(fn($f) => Str::startsWith($f, 'data_'));
        $latestStructure = $backupFiles->first(fn($f) => Str::startsWith($f, 'structure_'));

        $filesToDownload = collect([$latestStructure, $latestData])->filter();

        if (!file_exists(storage_path($this->localBackupPath))) {
            mkdir(storage_path($this->localBackupPath), 0755, true);
        }

        foreach ($filesToDownload as $file) {
            $this->info("Downloading {$file}...");
            $contents = $sftp->get($file);
            $localPath = storage_path("{$this->localBackupPath}/{$file}");
            file_put_contents($localPath, $contents);
            $this->info("Saved locally: {$localPath}");
        }

        $this->info('Download complete. Now restoring database...');

        // --- Restore SQL ---
        foreach ($filesToDownload as $file) {
            $localPath = storage_path("{$this->localBackupPath}/{$file}");
            $this->info("Restoring {$file}...");

            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');

            $command = "C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysql.exe -u {$dbUser} -p{$dbPass} {$dbName} < {$localPath}";
            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                $this->info("Successfully restored {$file}");
            } else {
                $this->error("Error restoring {$file}");
            }
        }

        // --- Media Backup ---
        $mediaFile = collect($files)->first(fn($f) => $f === 'media.zip');
        if ($mediaFile) {
            $this->info("Downloading {$mediaFile}...");
            $contents = $sftp->get($mediaFile);
            $localMediaZip = storage_path("{$this->localBackupPath}/{$mediaFile}");
            file_put_contents($localMediaZip, $contents);
            $this->info("Saved locally: {$localMediaZip}");

            $this->info('Restoring media folder...');

            $mediaPath = storage_path($this->localMediaPath);

            // Vorher bestehenden Media-Ordner löschen
            if (is_dir($mediaPath)) {
                File::deleteDirectory($mediaPath);
            }

            mkdir($mediaPath, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($localMediaZip) === true) {
                $zip->extractTo($mediaPath);
                $zip->close();
                $this->info("Media restored to {$mediaPath}");
            } else {
                $this->error("Failed to open {$localMediaZip}");
            }
        } else {
            $this->warn('No media.zip file found on SFTP server.');
        }

        $this->info('Database and media restore complete.');
        return 0;
    }
}
