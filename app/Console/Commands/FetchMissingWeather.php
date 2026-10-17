<?php

namespace App\Console\Commands;

use App\Jobs\FetchCatchWeather;
use App\Models\Catched;
use Illuminate\Console\Command;

class FetchMissingWeather extends Command
{
    // php artisan catches:fetch-weather --limit=200
    protected $signature = 'catches:fetch-weather
        {--limit=200 : Maximale Anzahl Fänge pro Lauf}
        {--retry-failed : Auch Fänge erneut abfragen, bei denen der Abruf fehlgeschlagen ist}';

    protected $description = 'Ergänzt fehlende Wetterdaten bei bestehenden Fängen (GeoSphere Austria)';

    // GeoSphere erlaubt 240 Abfragen pro Stunde – mit 16 s Abstand bleiben wir sicher darunter
    private const SPACING_SECONDS = 16;

    public function handle()
    {
        if (! config('services.geosphere.enabled')) {
            $this->warn('Wetterabruf ist deaktiviert (WEATHER_ENABLED=false).');
            return Command::SUCCESS;
        }

        $catches = Catched::query()
            ->where(function ($query) {
                $query->whereNull('weather_status');

                if ($this->option('retry-failed')) {
                    $query->orWhere('weather_status', 'failed');
                }
            })
            ->where('date', '<=', now()->subMinutes(FetchCatchWeather::AVAILABILITY_DELAY_MINUTES))
            ->orderByDesc('date')
            ->limit((int) $this->option('limit'))
            ->pluck('id');

        foreach ($catches->values() as $index => $id) {
            FetchCatchWeather::dispatch($id)->delay(now()->addSeconds($index * self::SPACING_SECONDS));
        }

        $minutes = (int) ceil($catches->count() * self::SPACING_SECONDS / 60);
        $this->info("{$catches->count()} Fänge eingeplant – fertig in etwa {$minutes} Minuten (Queue-Worker muss laufen).");

        return Command::SUCCESS;
    }
}
