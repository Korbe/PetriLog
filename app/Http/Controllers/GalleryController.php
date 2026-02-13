<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Catched;
use Illuminate\Support\Carbon;
use App\Http\Requests\FilterRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class GalleryController extends Controller
{
    public function index(FilterRequest $request)
    {
        session()->forget('meta');

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Bestimme den frühesten und spätesten Fang
        $dateRange = $user->catched()
            ->selectRaw('MIN(date) as earliest, MAX(date) as latest')
            ->first();

        $validated = $request->validated();

        $startDate = isset($validated['startDate'])
            ? Carbon::parse($validated['startDate'])->startOfDay()
            : Carbon::parse($dateRange->earliest)->startOfDay();

        $endDate = isset($validated['endDate'])
            ? Carbon::parse($validated['endDate'])->endOfDay()
            : Carbon::parse($dateRange->latest)->endOfDay();

        // Baue Query für alle Fänge inkl. Gewässer und Media
        $query = $user->catched()
            ->with(['media', 'lake', 'river']) // Lade Beziehungen eager
            ->whereBetween('date', [$startDate, $endDate]);

        if (!empty($validated['onlyWithDescription']) && filter_var($validated['onlyWithDescription'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNotNull('remark')->where('remark', '!=', '');
        }

        $catcheds = $query->get()
            ->filter(fn($catched) => $catched->hasMedia('photos'))
            ->map(function ($catched) {
                // Bestimme Gewässer
                $water = null;
                if ($catched->lake) {
                    $water = [
                        'type' => 'lake',
                        'id' => $catched->lake->id,
                        'name' => $catched->lake->name,
                    ];
                } elseif ($catched->river) {
                    $water = [
                        'type' => 'river',
                        'id' => $catched->river->id,
                        'name' => $catched->river->name,
                    ];
                }

                return [
                    'id' => $catched->id,
                    'name' => $catched->name,
                    'date' => $catched->date->format('d.m.Y'),
                    'images' => $catched->getMedia('photos')->map(fn($media) => [
                        'url' => $media->getFullUrl(),
                    ]),
                    'water' => $water, // Lake oder River
                ];
            })
            ->values();

        return Inertia::render('Gallery/Index', [
            'catcheds' => $catcheds,
            'dateRange' => [
                'startDate' => $startDate,
                'endDate' => $endDate
            ]
        ]);
    }
}
