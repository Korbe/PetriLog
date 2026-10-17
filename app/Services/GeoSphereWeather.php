<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Stündliche Wetteranalyse (INCA, 1 km Raster) von GeoSphere Austria.
 * Abgedeckt sind Österreich und die Grenzregionen; Daten ab März 2011, etwa eine Stunde verzögert.
 */
class GeoSphereWeather
{
    // Gebiet des INCA-Rasters (lat/lng)
    private const BBOX = ['lat_min' => 45.78, 'lat_max' => 49.47, 'lng_min' => 8.10, 'lng_max' => 17.74];

    private const FIRST_AVAILABLE = '2011-03-15 03:00:00';

    // Zeitraum vor dem Fang, über den Luftdrucktendenz und Niederschlag berechnet werden
    private const LOOKBACK_HOURS = 3;

    public static function covers(float $latitude, float $longitude): bool
    {
        return $latitude >= self::BBOX['lat_min'] && $latitude <= self::BBOX['lat_max']
            && $longitude >= self::BBOX['lng_min'] && $longitude <= self::BBOX['lng_max'];
    }

    public static function hasHistoryFor(CarbonInterface $date): bool
    {
        return $date->greaterThanOrEqualTo(Carbon::parse(self::FIRST_AVAILABLE, 'UTC'));
    }

    /**
     * Liefert die Werte zur vollen Stunde, die dem Fang am nächsten liegt.
     *
     * @return array<string, float|int|null>|null null, wenn für diese Stunde noch keine Daten vorliegen
     *
     * @throws RuntimeException|ConnectionException bei Netzwerk- oder Serverfehlern
     */
    public function at(CarbonInterface $date, float $latitude, float $longitude): ?array
    {
        $hour = Carbon::instance($date)->utc()->addMinutes(30)->startOfHour();

        $response = Http::timeout(15)
            ->acceptJson()
            ->withUserAgent('PetriLog/1.0 (+https://petrilog.com)')
            ->get(config('services.geosphere.url'), [
                'parameters' => 'T2M,P0,RH2M,UU,VV,RR',
                'start' => $hour->copy()->subHours(self::LOOKBACK_HOURS)->format('Y-m-d\TH:i'),
                'end' => $hour->format('Y-m-d\TH:i'),
                'lat_lon' => round($latitude, 5) . ',' . round($longitude, 5),
            ]);

        if ($response->status() === 429) {
            throw new RuntimeException('GeoSphere rate limit reached', 429);
        }

        // Für Zeitpunkte nach dem letzten Analyselauf antwortet die API mit einem Client-Fehler
        if ($response->clientError()) {
            return null;
        }

        if (! $response->successful()) {
            throw new RuntimeException("GeoSphere responded with HTTP {$response->status()}");
        }

        $timestamps = $response->json('timestamps', []);
        $values = $response->json('features.0.properties.parameters', []);
        $last = count($timestamps) - 1;

        if ($last < 0 || ! Carbon::parse($timestamps[$last])->equalTo($hour)) {
            return null;
        }

        $series = fn(string $key) => array_map(
            fn($v) => is_numeric($v) ? (float) $v : null,
            $values[$key]['data'] ?? []
        );

        $temperature = $series('T2M')[$last] ?? null;
        $pressure = $series('P0');
        $humidity = $series('RH2M')[$last] ?? null;
        $u = $series('UU')[$last] ?? null;
        $v = $series('VV')[$last] ?? null;
        $rain = array_slice($series('RR'), 1);

        $pressureNow = isset($pressure[$last]) ? $pressure[$last] / 100 : null;
        $pressureBefore = isset($pressure[0]) ? $pressure[0] / 100 : null;

        return [
            'weather_temperature' => $temperature !== null ? round($temperature, 1) : null,
            'weather_pressure' => $pressureNow !== null ? round($pressureNow, 1) : null,
            'weather_pressure_trend' => $pressureNow !== null && $pressureBefore !== null && $last >= self::LOOKBACK_HOURS
                ? round($pressureNow - $pressureBefore, 1)
                : null,
            'weather_humidity' => $humidity !== null ? (int) round(min(100, max(0, $humidity))) : null,
            'weather_wind_speed' => $u !== null && $v !== null ? round(sqrt($u ** 2 + $v ** 2) * 3.6, 1) : null,
            // Meteorologische Windrichtung: woher der Wind kommt, 0° = Nord
            'weather_wind_direction' => $u !== null && $v !== null && ($u != 0.0 || $v != 0.0)
                ? (int) round(fmod(rad2deg(atan2(-$u, -$v)) + 360, 360)) % 360
                : null,
            'weather_precipitation' => $rain && ! in_array(null, $rain, true) ? round(array_sum($rain), 1) : null,
        ];
    }
}
