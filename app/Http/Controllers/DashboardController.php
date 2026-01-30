<?php

namespace App\Http\Controllers;

use App\Models\Fish;
use App\Models\Lake;
use Inertia\Inertia;
use App\Models\River;
use App\Models\Catched;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        session()->forget('meta');

        $userId = Auth::id();

        $catchedStatsMonthly = $this->getCatchedStatisticsForPeriod(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $catchedStatsYearly = $this->getCatchedStatisticsForCurrentYear();

        $heaviestCatch = $this->heaviestCatch();
        $longestCatch = $this->longestCatch();

        $routeHeaviest = $heaviestCatch ? route("catched.show", $heaviestCatch->id) : "";
        $routeLongest = $longestCatch ? route("catched.show", $longestCatch->id) : "";

        $favoriteFish = $this->favoriteFish();
        $favoriteLocation = $this->favoriteLocations();
        $mostCatchesDay = $this->recordCatchesPerDay();
        $recentCatches = $this->recentCatches();

        return Inertia::render('Dashboard/Index', [
            'catchedCount' => Catched::where('user_id', Auth::id())->count(),
            'catchedStatsMonthly' => $catchedStatsMonthly,
            'catchedStatsYearly' => $catchedStatsYearly,
            'heaviestCatch' => $heaviestCatch,
            'longestCatch' => $longestCatch,

            'createUrl' => route('catched.create'),
            'routeHeaviest' => $routeHeaviest,
            'routeLongest' => $routeLongest,

            'favoriteFish' => $favoriteFish,
            'favoriteLocation' => $favoriteLocation,
            'mostCatchesDay' => $mostCatchesDay,
            'recentCatches' => $recentCatches,
        ]);
    }



    public function getCatchedStatisticsForPeriod(Carbon $startDate, Carbon $endDate)
    {
        $userId = Auth::id();

        // Hole alle Datumswerte und deren Anzahl gruppiert nach Tag aus der Tabelle
        $results = Catched::select(
            DB::raw('DATE(date) as day'),
            DB::raw('COUNT(*) as count')
        )
            ->where('user_id', $userId) // nur eigene Einträge
            ->whereBetween('date', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Array um die Daten nach Datum aufzubauen
        $timestamps = [];
        $counts = [];

        // Iteriere alle Tage im Zeitraum und fülle mit 0, wenn kein Eintrag vorliegt
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayString = $date->format('Y-m-d');
            $timestamps[] = $dayString;

            // Suche ob der Tag in den Ergebnissen ist
            $found = $results->firstWhere('day', $dayString);
            $counts[] = $found ? $found->count : 0;
        }

        // Gesamtanzahl der Catched Einträge im Zeitraum
        $total = $results->sum('count');

        return [
            'timestamps' => $timestamps,
            'scores' => $counts,
            'total' => $total,
        ];
    }

    public function getCatchedStatisticsForCurrentYear()
    {
        $userId = Auth::id();

        // Jahresanfang und -ende festlegen
        $startDate = now()->startOfYear();
        $endDate = now()->endOfYear();

        // Hole alle Datumswerte und deren Anzahl gruppiert nach Monat aus der Tabelle
        $results = Catched::select(
            DB::raw('MONTH(date) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('user_id', $userId) // nur eigene Einträge
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [];
        $counts = [];

        // Für jeden Monat von 1 bis 12
        for ($month = 1; $month <= 12; $month++) {
            // Monat als zweistellige Zahl z.B. "01", "02", ..., "12"
            $monthString = str_pad($month, 2, '0', STR_PAD_LEFT);
            // Optional: Monatsname (deutsch oder englisch)
            $months[] = $monthString;

            // Suche ob der Monat in den Ergebnissen ist
            $found = $results->firstWhere('month', $month);
            $counts[] = $found ? $found->count : 0;
        }

        // Gesamtanzahl der Catched Einträge im Jahr
        $total = $results->sum('count');

        return [
            'timestamps' => $months,
            'scores' => $counts,
            'total' => $total,
        ];
    }

    public function longestCatch()
    {
        $userId = Auth::id();

        $catched = Catched::where('user_id', $userId)
            ->orderByDesc('length')
            ->first();

        if ($catched != null)
            $catched->load('media');

        return $catched;
    }

    public function heaviestCatch()
    {
        $userId = Auth::id();

        $catched = Catched::where('user_id', $userId)
            ->orderByDesc('weight')
            ->first();

        if ($catched == null ||$catched->weight == null) {
            return null;
        }

        if ($catched != null)
            $catched->load('media');

        return $catched;
    }

    public function favoriteFish()
    {
        $userId = Auth::id();

        $favoriteFish = Catched::select('fish_id')
            ->where('user_id', $userId)
            ->groupBy('fish_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(1)
            ->first();

        if(!$favoriteFish) {
            return null;
        }

        $fish = Fish::find($favoriteFish->fish_id);

        return $fish->name;
    }

    public function favoriteLocations()
    {
        $userId = Auth::id();

        // ===== Top Lake =====
        $topLake = Catched::selectRaw('lake_id, COUNT(*) as total')
            ->where('user_id', $userId)
            ->whereNotNull('lake_id')
            ->groupBy('lake_id')
            ->orderByDesc('total')
            ->first();

        // ===== Top River =====
        $topRiver = Catched::selectRaw('river_id, COUNT(*) as total')
            ->where('user_id', $userId)
            ->whereNotNull('river_id')
            ->groupBy('river_id')
            ->orderByDesc('total')
            ->first();

        // Keine Daten vorhanden
        if (!$topLake && !$topRiver) {
            return null;
        }

        // ===== Vergleich =====
        if ($topLake && (!$topRiver || $topLake->total >= $topRiver->total)) {
            $lake = Lake::find($topLake->lake_id);

            return $lake->name;
        }

        $river = River::find($topRiver->river_id);

        return $river->name;
    }

    public function recordCatchesPerDay()
    {
        $userId = Auth::id();

        $record = Catched::select(
            DB::raw('DATE(date) as catch_date'),
            DB::raw('COUNT(*) as total')
        )
            ->where('user_id', $userId)
            ->groupBy(DB::raw('DATE(date)'))
            ->orderByDesc('total')
            ->first();

        if (!$record) {
            return null;
        }

        return $record->total;
    }

public function recentCatches()
{
    $userId = Auth::id();

    return Catched::with(['fish', 'lake', 'river'])
        ->where('user_id', $userId)
        ->orderByDesc('created_at')
        ->limit(2)
        ->get()
        ->map(function ($catched) {
            return [
                'id' => $catched->id,
                'created_at' => $catched->created_at,
                'fish' => $catched->fish,
                'lake' => $catched->lake,
                'river' => $catched->river,
                'show_url' => route('catched.show', $catched->id),
            ];
        });
}
}
