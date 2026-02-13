<?php

namespace App\Http\Controllers;

use App\Models\Fish;
use App\Models\Catched;
use App\Models\Lake;
use App\Models\River;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\FilterRequest;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Controllers\Controller;

class CatchedController extends Controller
{
    public function index(FilterRequest $request)
    {
        session()->forget('meta');

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $totalCatchedCount = $user->catched()->count();

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

        $query = $user->catched()
            ->with(['fish', 'lake', 'river'])
            ->whereBetween('date', [$startDate, $endDate]);

        if (!empty($validated['onlyWithDescription']) && filter_var($validated['onlyWithDescription'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNotNull('remark')->where('remark', '!=', '');
        }

        $catchedItems = $query
            ->orderBy('date', 'desc')
            ->get();

        $grouped = $catchedItems->groupBy(fn($item) => $item->date->format('d.m.Y'));

        return Inertia::render('Catched/Index', [
            'groupedCatcheds' => $grouped,
            'dateRange' => [
                'startDate' => $startDate,
                'endDate' => $endDate
            ],
            'totalCatchedCount' => $totalCatchedCount,
            'createUrl' => route('app.catched.create'),
            'currentUrl' => route('app.catched.index'),
        ]);
    }

    public function create()
    {
        session()->forget('meta');

        $user = Auth::user();
        $totalCatchedCount = $user->catched()->count();

        if (!$user->subscribed() && $totalCatchedCount >= 5) {
            return redirect()->route('app.catched.index')
                ->with('error', 'Du hast das Limit an Einträgen erreicht. Mit einem Jahresabo kannst du unbegrenzt Einträge erstellen.');
        }

        $fish = Fish::orderByRaw("
            name = 'Anderes / nicht gelistet' DESC,
            name ASC
        ")->get();

        $lakes = Lake::orderByRaw("
            name = 'Anderes / nicht gelistet' DESC,
            name ASC
        ")->get();

        $rivers = River::orderByRaw("
                name = 'd' DESC,
                name ASC
            ")->get();

        return Inertia::render('Catched/Create', [
            'fish' => $fish,
            'lakes' => $lakes,
            'rivers' => $rivers,
            'backToUrl' => route('app.catched.index'),
            'storeUrl' => route('app.catched.store'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'fish_id' => 'required|exists:fish,id',
            'lake_id' => 'nullable|exists:lakes,id|required_without:river_id',
            'river_id' => 'nullable|exists:rivers,id|required_without:lake_id',
            'length' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string',
            'weight' => 'nullable|integer',
            'depth' => 'nullable|integer',
            'temperature' => 'nullable|integer',
            'air_pressure' => 'nullable|integer',
            'bait' => 'nullable|string',
            'remark' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|max:102400',
        ], [
            'lake_id.required_without' => 'Bitte wähle entweder einen See oder einen Fluss aus.',
            'river_id.required_without' => 'Bitte wähle entweder einen Fluss oder einen See aus.',
        ]);

        $validated['user_id'] = Auth::id();

        $catch = Catched::create($validated);

        if ($request->hasFile('photos')) {
            collect($request->file('photos'))
                ->take(3)
                ->each(fn($photo) => $catch->addMedia($photo)->toMediaCollection('photos'));
        }

        return redirect()
            ->route('app.catched.show', $catch->id)
            ->with('success', 'Fang erfolgreich eingetragen.');
    }

    public function show(Catched $catched)
    {
        session()->forget('meta');
        $this->authorize('view', $catched);

        $shareurl = route('public.catched.show', $catched->id);
        $editurl = route('app.catched.edit', $catched->id);

        return Inertia::render('Catched/Show', [
            'catched' => $catched->load(['media', 'fish', 'lake', 'river']),
            'shareUrl' => $shareurl,
            'editUrl' => $editurl,
        ]);
    }

    public function edit(Catched $catched)
    {
        session()->forget('meta');
        $this->authorize('update', $catched);

        $catched->load(['media', 'fish', 'lake', 'river']);

        $rivers = River::orderByDesc('is_default')->orderBy('name')->get();
        $fish = Fish::orderByDesc('is_default')->orderBy('name')->get();
        $lakes  = Lake::orderByDesc('is_default')->orderBy('name')->get();

        return Inertia::render('Catched/Edit', [
            'catched' => $catched,
            'fish' => $fish,
            'lakes' => $lakes,
            'rivers' => $rivers,
        ]);
    }

    public function update(Request $request, Catched $catched)
    {
        $this->authorize('update', $catched);

        $validated = $request->validate([
            'date' => 'required|date',
            'fish_id' => 'required|exists:fish,id',
            'lake_id' => 'nullable|exists:lakes,id|required_without:river_id',
            'river_id' => 'nullable|exists:rivers,id|required_without:lake_id',
            'length' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string',
            'weight' => 'nullable|integer',
            'depth' => 'nullable|integer',
            'temperature' => 'nullable|integer',
            'air_pressure' => 'nullable|integer',
            'bait' => 'nullable|string',
            'remark' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|max:102400',
        ], [
            'lake_id.required_without' => 'Bitte wähle entweder einen See oder einen Fluss aus.',
            'river_id.required_without' => 'Bitte wähle entweder einen Fluss oder einen See aus.',
        ]);

        $existingCount = $catched->getMedia('photos')->count();
        $newPhotos = $request->file('photos', []);
        $remainingSlots = 3 - $existingCount;

        if (count($newPhotos) > $remainingSlots) {
            return back()->with(
                'error',
                "Du kannst maximal 3 Bilder hochladen. Bereits vorhanden: {$existingCount}"
            );
        }

        $catched->update($validated);

        foreach ($newPhotos as $photo) {
            $catched->addMedia($photo)->toMediaCollection('photos');
        }

        return redirect()
            ->route('app.catched.show', $catched->id)
            ->with('success', 'Fang erfolgreich aktualisiert.');
    }

    public function destroy(Catched $catched)
    {
        $this->authorize('delete', $catched);
        $catched->delete();

        return redirect()->route('app.catched.index')->with('success', 'Fang gelöscht.');
    }

    public function deletePhoto($mediaId)
    {
        $user = Auth::user();
        $media = Media::findOrFail($mediaId);
        $catched = Catched::findOrFail($media->model_id);

        if ($catched->user_id !== $user->id) abort(403);

        if ($media->collection_name === 'photos') $media->delete();

        return redirect()->back()->with('success', 'Bild gelöscht.');
    }
}
