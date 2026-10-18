<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Koordinaten → lesbare Adresse über OpenStreetMap Nominatim.
 *
 * Nutzungsregeln (operations.osmfoundation.org/policies/nominatim): eindeutiger User-Agent, höchstens
 * eine Anfrage pro Sekunde, Ergebnisse cachen. Positionen werden auf ~11 m gerundet, damit der Cache greift.
 */
class ReverseGeocoder
{
    public function address(float $lat, float $lng): ?string
    {
        if (! config('services.nominatim.enabled')) {
            return null;
        }

        $lat = round($lat, 4);
        $lng = round($lng, 4);

        return Cache::remember("geocode:{$lat},{$lng}", now()->addDays(30), fn() => $this->lookup($lat, $lng)) ?: null;
    }

    private function lookup(float $lat, float $lng): string
    {
        // Globale Bremse: höchstens eine Anfrage pro Sekunde an Nominatim. Die Sperre wird nicht freigegeben,
        // sondern läuft nach einer Sekunde ab – so wartet nur eine gleichzeitige zweite Anfrage.
        if (! Cache::lock('geocode:nominatim', 1)->block(3)) {
            return '';
        }

        try {
            $response = Http::timeout(6)
                ->withHeaders(['User-Agent' => 'PetriLog/1.0 (' . config('services.nominatim.email') . ')'])
                ->get(rtrim(config('services.nominatim.url'), '/') . '/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'jsonv2',
                    'zoom' => 17,
                    'addressdetails' => 1,
                    'accept-language' => 'de',
                ]);

            return $response->successful() ? $this->format($response->json() ?? []) : '';
        } catch (Throwable $e) {
            Log::warning('Adresse konnte nicht ermittelt werden', ['error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * Kurze Adresse wie „Seeweg 3, 9580 Drobollach, Österreich“ statt der langen Nominatim-Kette.
     */
    private function format(array $data): string
    {
        $a = $data['address'] ?? [];
        if (! $a) {
            return (string) ($data['display_name'] ?? '');
        }

        $street = trim(($a['road'] ?? $a['path'] ?? $a['footway'] ?? $a['water'] ?? $a['natural'] ?? '') . ' ' . ($a['house_number'] ?? ''));
        $place = $a['village'] ?? $a['town'] ?? $a['city'] ?? $a['hamlet'] ?? $a['municipality'] ?? '';
        $locality = trim(($a['postcode'] ?? '') . ' ' . $place);

        return implode(', ', array_filter([$street, $locality, $a['country'] ?? null]));
    }
}
