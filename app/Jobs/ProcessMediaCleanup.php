<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProcessMediaCleanup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function handle()
    {
        Log::info("Processing media {$this->media->id}");

        $originalPath = $this->media->getPath();
        $optimizedPath = $this->media->getPath('optimized');

        if (file_exists($originalPath)) {
            unlink($originalPath);
        }

        if (file_exists($optimizedPath)) {
            rename($optimizedPath, $originalPath);
        }

        $conversionDirectory = storage_path('app/public/' . $this->media->id . '/conversions');

        if (File::exists($conversionDirectory)) {
            File::deleteDirectory($conversionDirectory);
        }

        $this->media->size = File::size($originalPath);
        $this->media->save();
    }
}