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

        $validated = $request->validated();

        // Filter auf Catched mit Bildern
        $baseQuery = $user->catched()
            ->whereHas('media', fn($q) => $q->where('collection_name', 'photos'));

        // earliest / latest nur auf Catched mit Bildern
        $earliestDate = $user->catched()->min('date');
        $latestDate   = $user->catched()->max('date');

        $startDate = isset($validated['startDate'])
            ? Carbon::parse($validated['startDate'])->startOfDay()
            : Carbon::parse($earliestDate)->startOfDay();

        $endDate = isset($validated['endDate'])
            ? Carbon::parse($validated['endDate'])->endOfDay()
            : Carbon::parse($latestDate)->endOfDay();

        $query = $baseQuery
            ->with(['media', 'lake', 'river'])
            ->whereBetween('date', [$startDate, $endDate]);

        if (!empty($validated['onlyWithDescription']) && filter_var($validated['onlyWithDescription'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNotNull('remark')->where('remark', '!=', '');
        }

        $perPage = 8;
        $paginated = $query->orderBy('date', 'desc')->paginate($perPage)->withQueryString();

        $catcheds = $paginated->getCollection()
            ->map(fn($catched) => [
                'id' => $catched->id,
                'name' => $catched->name,
                'date' => $catched->date->format('d.m.Y'),
                'images' => $catched->getMedia('photos')->map(fn($media) => ['url' => $media->getFullUrl()]),
                'water' => $catched->lake
                    ? ['type' => 'lake', 'id' => $catched->lake->id, 'name' => $catched->lake->name]
                    : ($catched->river ? ['type' => 'river', 'id' => $catched->river->id, 'name' => $catched->river->name] : null)
            ]);

        $pagination = [
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'next_page_url' => $paginated->nextPageUrl(),
        ];

        return Inertia::render('Gallery/Index', [
            'catcheds' => $catcheds,
            'dateRange' => [
                'startDate' => $startDate,
                'endDate' => $endDate
            ],
            'pagination' => $pagination
        ]);
    }
}
