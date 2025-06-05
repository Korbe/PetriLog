<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Catched;
use Illuminate\Support\Carbon;
use App\Http\Requests\FilterRequest;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller
{
    public function index(FilterRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Bestimme den frühesten und spätesten Fang für diesen User
        $dateRange = $user->catched()
            ->selectRaw('MIN(date) as earliest, MAX(date) as latest')
            ->first();

        // Validiere das Request (startDate, endDate, onlyWithDescription)
        $validated = $request->validated();

        $startDate = isset($validated['startDate'])
            ? Carbon::parse($validated['startDate'])->startOfDay()
            : Carbon::parse($dateRange->earliest)->startOfDay();

        $endDate = isset($validated['endDate'])
            ? Carbon::parse($validated['endDate'])->endOfDay()
            : Carbon::parse($dateRange->latest)->endOfDay();

        // Baue Query für alle Fänge mit Bildern im gegebenen Zeitraum
        $query = $user->catched()
            ->with('media')
            ->whereBetween('date', [$startDate, $endDate]);

        // Optionaler Filter: nur mit Beschreibung
        if (!empty($validated['onlyWithDescription']) && filter_var($validated['onlyWithDescription'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNotNull('remark')->where('remark', '!=', '');
        }

        $catcheds = $query->get()
            ->filter(fn($catched) => $catched->hasMedia('photos'))
            ->map(function ($catched) {
                return [
                    'id' => $catched->id,
                    'name' => $catched->name,
                    'waters' => $catched->waters,
                    'date' => $catched->date->format('d.m.Y'),
                    'images' => $catched->getMedia('photos')->map(function ($media) {
                        return [
                            'url' => $media->getFullUrl(),
                        ];
                    }),
                ];
            })
            ->values(); // optional: index neu ordnen

        return Inertia::render('Gallery/Index', [
            'catcheds' => $catcheds,
            'dateRange' => [
                'startDate' => $startDate,
                'endDate' => $endDate
            ]
        ]);
    }
}
