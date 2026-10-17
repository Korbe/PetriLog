<?php

namespace App\Http\Controllers;

use App\Models\Catched;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Jahres-Fangmeldung für Revier bzw. Verein: Übersicht, CSV-Export und Druckansicht.
 */
class ReportController extends Controller
{
    private const TIMEZONE = 'Europe/Vienna';

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        [$year, $water] = $this->filters($request, $user);

        $yearCatches = $this->catches($user, $year);
        $catches = $this->filterWater($yearCatches, $water);

        return Inertia::render('Report/Index', [
            'years' => $this->years($user),
            'year' => $year,
            'water' => $water,
            'waters' => $yearCatches
                ->map(fn(Catched $c) => ['key' => $this->waterKey($c), 'name' => $this->waterName($c)])
                ->unique('key')
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),
            'totals' => [
                'count' => $catches->count(),
                'kept' => $catches->whereStrict('released', false)->count(),
                'released' => $catches->whereStrict('released', true)->count(),
                'unknown' => $catches->whereNull('released')->count(),
                'keptWeight' => (int) $catches->whereStrict('released', false)->sum('weight'),
            ],
            'summary' => $this->summary($catches),
            'catches' => $catches->map(fn(Catched $c) => [
                'id' => $c->id,
                'date' => $c->date,
                'fish' => $c->fish?->name ?? 'Unbekannter Fisch',
                'water' => $this->waterName($c),
                'length' => $c->length,
                'weight' => $c->weight,
                'released' => $c->released,
            ])->values(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        /** @var User $user */
        $user = Auth::user();
        [$year, $water] = $this->filters($request, $user);
        $catches = $this->filterWater($this->catches($user, $year), $water);

        $filename = "petrilog-fangmeldung-{$year}.csv";

        return response()->streamDownload(function () use ($catches) {
            $out = fopen('php://output', 'w');
            // BOM, damit Excel Umlaute korrekt anzeigt
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Datum', 'Uhrzeit', 'Fischart', 'Gewässer', 'Länge (cm)', 'Gewicht (g)', 'Verbleib', 'Köder', 'Breitengrad', 'Längengrad'], ';');

            foreach ($catches as $c) {
                $local = $c->date->copy()->setTimezone(self::TIMEZONE);
                fputcsv($out, [
                    $local->format('d.m.Y'),
                    $local->format('H:i'),
                    $c->fish?->name ?? 'Unbekannter Fisch',
                    $this->waterName($c),
                    $c->length,
                    $c->weight,
                    $this->releaseLabel($c->released),
                    $this->csvSafe($c->bait),
                    number_format((float) $c->latitude, 6, ',', ''),
                    number_format((float) $c->longitude, 6, ',', ''),
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function print(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        [$year, $water] = $this->filters($request, $user);
        $catches = $this->filterWater($this->catches($user, $year), $water);

        return view('reports.catches', [
            'user' => $user,
            'year' => $year,
            'waterName' => $water ? $this->waterName($catches->first()) : null,
            'summary' => $this->summary($catches),
            'catches' => $catches,
            'timezone' => self::TIMEZONE,
            'releaseLabel' => fn(?bool $released) => $this->releaseLabel($released),
            'waterNameFor' => fn(Catched $c) => $this->waterName($c),
        ]);
    }

    /**
     * Verbleib direkt aus der Übersicht nachtragen.
     */
    public function updateRelease(Request $request, Catched $catched)
    {
        $this->authorize('update', $catched);

        $validated = $request->validate(['released' => 'nullable|boolean']);

        $catched->update(['released' => $validated['released'] ?? null]);

        return back();
    }

    /**
     * @return array{0: int, 1: ?string}
     */
    private function filters(Request $request, User $user): array
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'water' => ['nullable', 'string', 'regex:/^(lake|river)-\d+$/'],
        ]);

        $year = (int) ($validated['year'] ?? ($this->years($user)[0] ?? now(self::TIMEZONE)->year));

        return [$year, $validated['water'] ?? null];
    }

    private function years(User $user): array
    {
        $first = $user->catched()->min('date');
        $last = $user->catched()->max('date');

        if (! $first || ! $last) {
            return [now(self::TIMEZONE)->year];
        }

        return range(
            Carbon::parse($last, 'UTC')->setTimezone(self::TIMEZONE)->year,
            Carbon::parse($first, 'UTC')->setTimezone(self::TIMEZONE)->year
        );
    }

    /**
     * Alle Fänge eines Kalenderjahres (österreichische Zeit), älteste zuerst.
     */
    private function catches(User $user, int $year): Collection
    {
        $start = Carbon::create($year, 1, 1, 0, 0, 0, self::TIMEZONE)->utc();
        $end = Carbon::create($year + 1, 1, 1, 0, 0, 0, self::TIMEZONE)->utc();

        return $user->catched()
            ->with(['fish', 'lake', 'river'])
            ->where('date', '>=', $start)
            ->where('date', '<', $end)
            ->orderBy('date')
            ->get();
    }

    private function filterWater(Collection $catches, ?string $water): Collection
    {
        return $water
            ? $catches->filter(fn(Catched $c) => $this->waterKey($c) === $water)->values()
            : $catches;
    }

    /**
     * Gewässer → Fischarten mit Anzahl entnommen / zurückgesetzt.
     */
    private function summary(Collection $catches): Collection
    {
        return $catches
            ->groupBy(fn(Catched $c) => $this->waterKey($c))
            ->map(fn(Collection $group) => [
                'water' => $this->waterName($group->first()),
                'count' => $group->count(),
                'fish' => $group
                    ->groupBy(fn(Catched $c) => $c->fish?->name ?? 'Unbekannter Fisch')
                    ->map(fn(Collection $fish, string $name) => [
                        'name' => $name,
                        'count' => $fish->count(),
                        'kept' => $fish->whereStrict('released', false)->count(),
                        'released' => $fish->whereStrict('released', true)->count(),
                        'unknown' => $fish->whereNull('released')->count(),
                        'keptWeight' => (int) $fish->whereStrict('released', false)->sum('weight'),
                        'maxLength' => (int) $fish->max('length'),
                    ])
                    ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values(),
            ])
            ->sortBy('water', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function waterKey(Catched $c): string
    {
        return $c->lake_id ? "lake-{$c->lake_id}" : "river-{$c->river_id}";
    }

    private function waterName(?Catched $c): string
    {
        return $c?->lake?->name ?? $c?->river?->name ?? 'Unbekanntes Gewässer';
    }

    // Verhindert, dass Freitext in Excel als Formel ausgeführt wird
    private function csvSafe(?string $value): ?string
    {
        return $value !== null && preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }

    private function releaseLabel(?bool $released): string
    {
        return match ($released) {
            true => 'Zurückgesetzt',
            false => 'Entnommen',
            null => 'Nicht angegeben',
        };
    }
}
