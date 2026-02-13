<?php

namespace Tests\Feature\Listener;

use App\Jobs\ProcessMediaCleanup;
use App\Listeners\ConversionHasBeenCompletedListener;
use App\Models\Catched;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompletedEvent;
use Tests\TestCase;

class ConversionHasBeenCompletedListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_dispatches_cleanup_job()
    {
        Queue::fake();

        // Catched mit Media erstellen
        $catched = Catched::factory()->create();
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $catched->addMedia($file)->toMediaCollection('photos');

        // Echte Conversion instanziieren (minimal)
        $conversion = new Conversion('optimized');

        // Event erzeugen
        $event = new ConversionHasBeenCompletedEvent($media, $conversion);

        // Listener aufrufen
        $listener = new ConversionHasBeenCompletedListener();
        $listener->handle($event);

        // Prüfen, dass Job in die Queue gestellt wurde
        Queue::assertPushed(ProcessMediaCleanup::class, function ($job) use ($media) {
            return $job->media->id === $media->id;
        });
    }
}
