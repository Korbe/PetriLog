<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WikiExporter;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use RuntimeException;
use ZipArchive;

/**
 * Download des Wiki-Inhalts im Seeder-Format, um ihn ins Repo (database/seeders) zu übernehmen.
 */
class WikiExportAdminController extends Controller
{
    public function index(WikiExporter $exporter)
    {
        $datasets = collect(WikiExporter::DATASETS)->map(fn(string $name) => [
            'name' => $name,
            'file' => "{$name}.json",
            'count' => count($exporter->dataset($name)),
        ]);

        return Inertia::render('Admin/WikiExport/Index', [
            'datasets' => $datasets,
            'zipAvailable' => class_exists(ZipArchive::class),
        ]);
    }

    /**
     * Alles als ZIP: data/*.json + assets/… – direkt nach database/seeders entpackbar.
     */
    public function zip(WikiExporter $exporter)
    {
        abort_unless(class_exists(ZipArchive::class), 501, 'Die PHP-Erweiterung „zip“ ist auf dem Server nicht installiert.');

        File::ensureDirectoryExists(storage_path('app/tmp'));
        $path = storage_path('app/tmp/wiki-export-' . uniqid() . '.zip');

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('ZIP-Datei konnte nicht erstellt werden.');
        }

        foreach ($exporter->files() as $file => $contents) {
            $zip->addFromString($file, $contents);
        }
        $zip->close();

        return response()
            ->download($path, 'petrilog-wiki-' . now('Europe/Vienna')->format('Y-m-d') . '.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend();
    }

    /**
     * Einzelne Seed-Datei (ohne Bilder).
     */
    public function dataset(WikiExporter $exporter, string $name)
    {
        abort_unless(in_array($name, WikiExporter::DATASETS, true), 404);

        return response()->streamDownload(
            fn() => print($exporter->json($exporter->dataset($name))),
            "{$name}.json",
            ['Content-Type' => 'application/json; charset=UTF-8'],
        );
    }
}
