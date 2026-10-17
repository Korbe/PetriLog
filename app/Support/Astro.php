<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Mondphase und Sonnenstand zu einem Fangzeitpunkt – rein rechnerisch, ohne externe API.
 */
class Astro
{
    private const SYNODIC_MONTH = 29.530588853;

    // Referenz-Neumond: 6. Jänner 2000, 18:14 UTC (Julianisches Datum)
    private const REFERENCE_NEW_MOON_JD = 2451550.1;

    private const TIMEZONE = 'Europe/Vienna';

    public static function forCatch(CarbonInterface $date, float $latitude, float $longitude): array
    {
        return [
            'moon' => self::moon($date),
            'sun' => self::sun($date, $latitude, $longitude),
        ];
    }

    /**
     * @return array{phase: float, illumination: int, name: string, waxing: bool}
     */
    public static function moon(CarbonInterface $date): array
    {
        $julianDate = $date->getTimestamp() / 86400 + 2440587.5;
        $phase = fmod(($julianDate - self::REFERENCE_NEW_MOON_JD) / self::SYNODIC_MONTH, 1.0);
        if ($phase < 0) {
            $phase += 1.0;
        }

        return [
            'phase' => round($phase, 3),
            'illumination' => (int) round((1 - cos(2 * M_PI * $phase)) / 2 * 100),
            'name' => self::moonName($phase),
            'waxing' => $phase < 0.5,
        ];
    }

    /**
     * @return array{sunrise: ?string, sunset: ?string, daytime: string}
     */
    public static function sun(CarbonInterface $date, float $latitude, float $longitude): array
    {
        $local = Carbon::instance($date)->setTimezone(self::TIMEZONE);
        $info = date_sun_info($local->copy()->setTime(12, 0)->getTimestamp(), $latitude, $longitude);

        $sunrise = is_int($info['sunrise'] ?? null) ? $info['sunrise'] : null;
        $sunset = is_int($info['sunset'] ?? null) ? $info['sunset'] : null;

        return [
            'sunrise' => $sunrise ? Carbon::createFromTimestamp($sunrise, self::TIMEZONE)->format('H:i') : null,
            'sunset' => $sunset ? Carbon::createFromTimestamp($sunset, self::TIMEZONE)->format('H:i') : null,
            'daytime' => self::daytime($date->getTimestamp(), $sunrise, $sunset),
        ];
    }

    private static function daytime(int $time, ?int $sunrise, ?int $sunset): string
    {
        if (! $sunrise || ! $sunset) {
            return 'Tag';
        }

        $hour = 3600;

        return match (true) {
            abs($time - $sunrise) <= $hour => 'Morgendämmerung',
            abs($time - $sunset) <= $hour => 'Abenddämmerung',
            $time > $sunrise && $time < $sunset => 'Tag',
            default => 'Nacht',
        };
    }

    private static function moonName(float $phase): string
    {
        $names = [
            'Neumond',
            'Zunehmende Sichel',
            'Erstes Viertel',
            'Zunehmender Mond',
            'Vollmond',
            'Abnehmender Mond',
            'Letztes Viertel',
            'Abnehmende Sichel',
        ];

        // Acht gleich große Abschnitte, jeweils um die Hauptphase zentriert
        return $names[(int) floor(fmod($phase * 8 + 0.5, 8))];
    }
}
