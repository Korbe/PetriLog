<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProcessMediaCleanup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function handle(): void
    {
        try {
            Log::info("Processing media {$this->media->id}");

            $disk = $this->media->disk;

            // Relative Pfade (wichtig für Storage!)
            $originalPath = $this->media->getPathRelativeToRoot();
            $optimizedPath = $this->media->getPathRelativeToRoot('optimized');

            $storage = Storage::disk($disk);

            // Original löschen
            if ($storage->exists($originalPath)) {
                $storage->delete($originalPath);
            }

            // Optimized → Original verschieben
            if ($storage->exists($optimizedPath)) {
                $storage->move($optimizedPath, $originalPath);
            }

            // Conversions-Ordner löschen
            $conversionDirectory = $this->media->id . '/conversions';

            if ($storage->exists($conversionDirectory)) {
                $storage->deleteDirectory($conversionDirectory);
            }

            // Neue Dateigröße setzen
            if ($storage->exists($originalPath)) {
                $this->media->size = $storage->size($originalPath);
                $this->media->save();
            }

        } catch (Exception $e) {
            Log::error("Media cleanup failed for {$this->media->id}: {$e->getMessage()}");
            throw $e;
        }
    }
}
