<?php

namespace App\Listeners;

use App\Jobs\ProcessMediaCleanup;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompletedEvent;

class ConversionHasBeenCompletedListener
{
    public function handle(ConversionHasBeenCompletedEvent $event): void
    {
        // Dispatch a queued job to cleanup the media
        ProcessMediaCleanup::dispatch($event->media);
    }
}
