<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Catched;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\FilterRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CatchedController extends Controller
{
    public function index(FilterRequest $request)
    {
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
            ->whereBetween('date', [$startDate, $endDate]);

        if (!empty($validated['onlyWithDescription']) && filter_var($validated['onlyWithDescription'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNotNull('remark')->where('remark', '!=', '');
        }

        $catchedItems = $query
            ->orderBy('date', 'desc')
            ->get();

        // Gruppiere nach Datum (nur das Datum, nicht Zeit)
        $grouped = $catchedItems->groupBy(fn($item) => $item->date->format('d.m.Y'));

        return Inertia::render('Catched/Index', [
            'groupedCatcheds' => $grouped,
            'dateRange' => [
                'startDate' => $startDate,
                'endDate' => $endDate
            ],
            'totalCatchedCount' => $totalCatchedCount,
        ]);
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Wenn User noch im Trial ist und mehr als 5 catched-Einträge hat → redirect
        if ($user->onTrial() && $user->catched()->count() >= 5) {
            return redirect()->route('catched.index')
                ->with('error', 'Du hast das Limit deiner Testphase erreicht. Mit einem Jahresabo kannst du unbegrenzt Einträge erstellen.');
        }

        return Inertia::render('Catched/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string',
            'waters' => 'required|string',
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
            'photos.*' => 'nullable|image|max:30720',
        ]);

        $validated['user_id'] = Auth::id();

        $catch = Catched::create($validated);

        if ($request->hasFile('photos')) {
            collect($request->file('photos'))->take(3)->each(function ($photo) use ($catch) {
                $media = $catch->addMedia($photo)->toMediaCollection('photos');
                $this->UnLinkOptimizeImageAndCleanup($media);
            });
        }

        return redirect()->route('catched.show', $catch->id)->with('success', 'Fang erfolgreich eingetragen.');
    }

    public function show(Catched $catched)
    {
        $this->authorize('view', $catched);

        return Inertia::render('Catched/Show', [
            'catched' => $catched->load('media'),
        ]);
    }

    public function edit(Catched $catched)
    {
        $this->authorize('update', $catched);

        $catched->load('media');

        return Inertia::render('Catched/Edit', [
            'catched' => $catched
        ]);
    }

    public function update(Request $request, Catched $catched)
    {
        $this->authorize('update', $catched);

        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string',
            'waters' => 'required|string',
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
            'photos.*' => 'nullable|image|max:30720',
        ]);

        $existingCount = $catched->getMedia('photos')->count();
        $newPhotos = $request->file('photos', []);

        $remainingSlots = 3 - $existingCount;

        if (count($newPhotos) > $remainingSlots) {
            return redirect()->back()->with('error', 'Du kannst maximal 3 Bilder hochladen. Es sind bereits ' . $existingCount . ' vorhanden.');
        }

        $catched->update($validated);

        foreach ($newPhotos as $photo) {
            $media = $catched->addMedia($photo)->toMediaCollection('photos');
            $this->UnLinkOptimizeImageAndCleanup($media);
        }


        return redirect()->route('catched.show', $catched->id)->with('success', 'Fang erfolgreich aktualisiert.');
    }

    public function destroy(Catched $catched)
    {
        $this->authorize('delete', $catched);
        $catched->delete();

        return redirect()->route('catched.index')->with('success', 'Fang gelöscht.');
    }

    public function deletePhoto(Request $request, $mediaId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Media-Objekt finden
        $media = Media::findOrFail($mediaId);

        // Hole zugehörigen Catched-Eintrag
        $catched = Catched::findOrFail($media->model_id);

        // Sicherheit: Gehört der Catched-Eintrag dem eingeloggten Benutzer?
        if ($catched->user_id !== $user->id) {
            abort(403, 'Unauthorized to delete this photo.');
        }

        // Nur löschen, wenn es wirklich aus der 'photos'-Collection ist
        if ($media->collection_name === 'photos') {
            $media->delete();
        }

        return redirect()->back()->with('success', 'Bild gelöscht.');
    }

    public function UnLinkOptimizeImageAndCleanup($media)
    {
        $originalPath = $media->getPath();
        $optimizedPath = $media->getPath('optimized');

        if (file_exists($originalPath)) {
            unlink($originalPath);
        }

        if (file_exists($optimizedPath)) {
            rename($optimizedPath, $originalPath);
        }

        $conversionDirectory = storage_path('app/public/' . $media->id . '/conversions');

        if (File::exists($conversionDirectory)) {
            File::deleteDirectory($conversionDirectory);
        }

        $media->size = File::size($originalPath);
        $media->save();
    }
}
