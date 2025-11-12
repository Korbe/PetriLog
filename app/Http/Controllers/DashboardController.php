<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
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

        $catchedStatsMonthly = $this->getCatchedStatisticsForPeriod(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $catchedStatsYearly = $this->getCatchedStatisticsForCurrentYear();

        $heaviestCatch = $this->heaviestCatch();
        $longestCatch = $this->longestCatch();

        $routeHeaviest = $heaviestCatch ? route("catched.show", $heaviestCatch->id) : "";
        $routeLongest = $longestCatch ? route("catched.show", $longestCatch->id) : "";

        return Inertia::render('Dashboard/Dashboard', [
            'catchedStatsMonthly' => $catchedStatsMonthly,
            'catchedStatsYearly' => $catchedStatsYearly,
            'heaviestCatch' => $heaviestCatch,
            'longestCatch' => $longestCatch,

            'createUrl' => route('catched.create'),
            'routeHeaviest' => $routeHeaviest,
            'routeLongest' => $routeLongest,
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

        if ($catched != null)
            $catched->load('media');

        return $catched;
    }
}
