<?php

namespace App\Console\Commands;

use App\Services\WikiExporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportWiki extends Command
{
    // php artisan wiki:export            → schreibt nach database/seeders (data/*.json + assets/…)
    // php artisan wiki:export --no-images
    protected $signature = 'wiki:export
        {--path= : Zielordner (Standard: database/seeders)}
        {--no-images : Nur die JSON-Dateien schreiben}';

    protected $description = 'Exportiert Bundesländer, Vereine, Fische, Seen und Flüsse im Seeder-Format';

    public function handle(WikiExporter $exporter)
    {
        $target = rtrim($this->option('path') ?: database_path('seeders'), '/\\');
        $files = $exporter->files(withImages: ! $this->option('no-images'));

        foreach ($files as $relative => $contents) {
            $path = $target . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $contents);
        }

        $json = collect($files)->keys()->filter(fn($f) => str_starts_with($f, 'data/'))->count();
        $this->info("{$json} JSON-Dateien und " . (count($files) - $json) . " Bilder nach {$target} geschrieben.");
        $this->line('Änderungen prüfen mit: git diff --stat database/seeders');

        return Command::SUCCESS;
    }
}
