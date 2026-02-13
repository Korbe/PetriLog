<?php

namespace Tests\Feature\Job;

use App\Jobs\ProcessMediaCleanup;
use App\Listeners\ConversionHasBeenCompletedListener;
use App\Models\Catched;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompletedEvent;
use Tests\TestCase;

class ProcessMediaCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_dispatches_cleanup_job(): void
    {
        Queue::fake();

        // Fake Media-Objekt erzeugen
        $catched = Catched::factory()->create();
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $catched->addMedia($file)->toMediaCollection('photos');

        // Event erzeugen – Conversion mock erzeugen
        $conversion = Mockery::mock(Conversion::class);
        $event = new ConversionHasBeenCompletedEvent($media, $conversion);

        // Listener aufrufen
        $listener = new ConversionHasBeenCompletedListener();
        $listener->handle($event);

        // Prüfen, ob Job dispatched wurde
        Queue::assertPushed(ProcessMediaCleanup::class, function ($job) use ($media) {
            return $job->media->id === $media->id;
        });
    }

    public function test_process_media_cleanup_job_handles_files(): void
    {
        $diskName = config('media-library.disk_name');

        // Fake Disk
        Storage::fake($diskName);

        // Datei erstellen
        $file = UploadedFile::fake()->image('original.jpg');

        $catched = Catched::factory()->create();

        $media = $catched
            ->addMedia($file)
            ->toMediaCollection('photos');

        // Original- und Optimized-Dateien (relativ zum Disk root)
        $original = $media->getPathRelativeToRoot();
        $optimized = $media->getPathRelativeToRoot('optimized');

        // Optimized-Datei fake erzeugen
        Storage::disk($diskName)->put($optimized, 'optimized-content');

        // Sicherstellen, dass Optimized existiert
        $this->assertTrue(Storage::disk($diskName)->exists($optimized));
        $this->assertTrue(Storage::disk($diskName)->exists($original));

        // Job ausführen
        $job = new ProcessMediaCleanup($media);
        $job->handle();

        // Prüfen, dass Original immer noch existiert
        $this->assertTrue(Storage::disk($diskName)->exists($original));

        // Prüfen, dass Optimized jetzt weg ist
        $this->assertFalse(Storage::disk($diskName)->exists($optimized));
    }
}
