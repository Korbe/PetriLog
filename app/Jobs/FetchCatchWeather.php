<?php

namespace App\Jobs;

use App\Mail\WeatherReadyMail;
use App\Models\Catched;
use App\Services\GeoSphereWeather;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Holt Lufttemperatur, Luftdruck(-tendenz), Wind, Feuchte und Niederschlag zum Fangzeitpunkt.
 * Die Analyse ist erst etwa eine Stunde nach dem Fang verfügbar – bis dahin wird der Job verschoben.
 */
class FetchCatchWeather implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 6;

    // Wartezeit, bis die Stunde des Fangs von GeoSphere analysiert ist
    public const AVAILABILITY_DELAY_MINUTES = 90;

    public function __construct(public int $catchedId)
    {
    }

    /**
     * Startet den Abruf – bei frischen Fängen verzögert, damit die Daten schon vorliegen.
     */
    public static function dispatchFor(Catched $catched): void
    {
        if (! config('services.geosphere.enabled')) {
            return;
        }

        $readyAt = $catched->date->copy()->addMinutes(self::AVAILABILITY_DELAY_MINUTES);

        static::dispatch($catched->id)->delay($readyAt->isFuture() ? $readyAt : null);
    }

    public function handle(GeoSphereWeather $weather): void
    {
        $catched = Catched::find($this->catchedId);

        if (! $catched || ! config('services.geosphere.enabled')) {
            return;
        }

        $lat = (float) $catched->latitude;
        $lng = (float) $catched->longitude;

        if (! GeoSphereWeather::covers($lat, $lng) || ! GeoSphereWeather::hasHistoryFor($catched->date)) {
            $this->store($catched, [], 'unavailable');
            return;
        }

        if ($catched->date->copy()->addMinutes(self::AVAILABILITY_DELAY_MINUTES)->isFuture()) {
            $this->release($catched->date->copy()->addMinutes(self::AVAILABILITY_DELAY_MINUTES));
            return;
        }

        try {
            $values = $weather->at($catched->date, $lat, $lng);
        } catch (Throwable $e) {
            Log::warning("Weather fetch for catch {$catched->id} failed: {$e->getMessage()}");
            $this->retryOrGiveUp($catched, $e->getCode() === 429 ? 900 : 1800);
            return;
        }

        if ($values === null) {
            // Analyse für diese Stunde noch nicht veröffentlicht
            $this->retryOrGiveUp($catched, 3600);
            return;
        }

        $this->store($catched, $values, 'ok');
    }

    private function retryOrGiveUp(Catched $catched, int $delaySeconds): void
    {
        if ($this->attempts() < $this->tries) {
            $this->release($delaySeconds);
            return;
        }

        $this->store($catched, [], 'failed');
    }

    private function store(Catched $catched, array $values, string $status): void
    {
        $notify = (bool) $catched->weather_notify;

        $catched->forceFill([
            ...$values,
            'weather_status' => $status,
            'weather_fetched_at' => now(),
            // Benachrichtigung gibt es nur einmal – auch wenn keine Daten kommen, ist der Wunsch erledigt
            'weather_notify' => false,
        ])->saveQuietly();

        if ($status === 'ok' && $notify && $catched->user?->email) {
            Mail::to($catched->user)->queue(new WeatherReadyMail($catched));
        }
    }
}
