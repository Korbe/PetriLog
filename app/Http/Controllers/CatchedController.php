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
        // Session-Zustand zurücksetzen (falls nötig)
        session()->forget('meta');

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Gesamtanzahl aller Fänge
        $totalCatchedCount = $user->catched()->count();

        // Frühestes und spätestes Fangdatum
        $earliestDate = $user->catched()->min('date');
        $latestDate   = $user->catched()->max('date');

        // Wenn noch keine Fänge vorhanden
        if (!$earliestDate || !$latestDate) {
            return Inertia::render('Catched/Index', [
                'groupedCatcheds' => [],
                'dateRange'       => null,
                'totalCatchedCount' => 0,
                'createUrl'       => route('app.catched.create'),
                'currentUrl'      => route('app.catched.index'),
            ]);
        }

        // Validierte Filter aus Request
        $validated = $request->validated();

        // Start- & Enddatum für Filter
        $startDate = isset($validated['startDate'])
            ? Carbon::parse($validated['startDate'])->startOfDay()
            : Carbon::parse($earliestDate)->startOfDay();

        $endDate = isset($validated['endDate'])
            ? Carbon::parse($validated['endDate'])->endOfDay()
            : Carbon::parse($latestDate)->endOfDay();

        // Grundabfrage mit Beziehungen
        $query = $user->catched()
            ->with(['fish', 'lake', 'river'])
            ->whereBetween('date', [$startDate, $endDate]);

        // Optional: nur Fänge mit Beschreibung
        if (!empty($validated['onlyWithDescription'] ?? false)) {
            $query->whereNotNull('remark')->where('remark', '!=', '');
        }

        // Daten laden und nach Datum absteigend sortieren
        $catchedItems = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        // Gruppierung nach Tag
        $grouped = $catchedItems->getCollection()
            ->groupBy(fn($item) => $item->date->format('d.m.Y'));

        // Inertia Response
        return Inertia::render('Catched/Index', [
            'groupedCatcheds'  => $grouped,
            'dateRange'        => [
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ],
            'totalCatchedCount' => $totalCatchedCount,
            'createUrl'        => route('app.catched.create'),
            'currentUrl'       => route('app.catched.index'),
            'pagination' => [
                'current_page' => $catchedItems->currentPage(),
                'last_page' => $catchedItems->lastPage(),
                'next_page_url' => $catchedItems->nextPageUrl(),
            ],
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
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'image|max:102400',
        ], [
            'lake_id.required_without' => 'Bitte wähle entweder einen See oder einen Fluss aus.',
            'river_id.required_without' => 'Bitte wähle entweder einen Fluss oder einen See aus.',
        ]);

        $validated['user_id'] = Auth::id();

        $catched = Catched::create($validated);

        /**
         * Upload + Reihenfolge speichern
         */
        if ($request->hasFile('photos')) {
            collect($request->file('photos'))
                ->values() // Index sauber 0,1,2
                ->take(3)
                ->each(function ($photo, $index) use ($catched) {
                    $catched
                        ->addMedia($photo)
                        ->toMediaCollection('photos')
                        ->update([
                            'order_column' => $index,
                        ]);
                });
        }

        return redirect()
            ->route('app.catched.show', $catched->id)
            ->with('success', 'Fang erfolgreich eingetragen.');
    }

    public function show(Catched $catched)
    {
        session()->forget('meta');
        $this->authorize('view', $catched);

        $shareurl = route('public.catched.show', $catched->id);
        $editurl = route('app.catched.edit', $catched->id);

        // Medien sortieren nach order_column
        $catched->load(['fish', 'lake', 'river']); // lade Relationen
        $catched->media = collect($catched->getMedia('photos'))
            ->sortBy(fn($media) => (int) $media->order_column) 
            ->values()                                         
            ->map(fn($media) => $media->toArray());

        return Inertia::render('Catched/Show', [
            'catched' => $catched,
            'shareUrl' => $shareurl,
            'editUrl' => $editurl,
        ]);
    }

    public function edit(Catched $catched)
    {
        session()->forget('meta');
        $this->authorize('update', $catched);

        // Lade Relationen
        $catched->load(['media', 'fish', 'lake', 'river']);

        // Listen für Multiselects
        $rivers = River::orderByDesc('is_default')->orderBy('name')->get();
        $fish = Fish::orderByDesc('is_default')->orderBy('name')->get();
        $lakes  = Lake::orderByDesc('is_default')->orderBy('name')->get();

        return Inertia::render('Catched/Edit', [
            'asdf' => 'asdf',
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
            'photos.*' => 'image|max:102400',
            'photo_order' => 'nullable|array',
        ]);

        // Basisdaten speichern
        $catched->update($validated);

        $existingMedia = $catched->getMedia('photos')->keyBy('id');
        $newFiles = collect($request->file('photos', []));

        $order = $request->input('photo_order', []);
        $orderedIds = [];

        foreach ($order as $key) {
            if (str_starts_with($key, 'media-')) {
                $id = (int) str_replace('media-', '', $key);
                if ($existingMedia->has($id)) {
                    $orderedIds[] = $id;
                }
            }

            if (str_starts_with($key, 'file-')) {
                $file = $newFiles->shift();
                if ($file) {
                    $media = $catched->addMedia($file)->toMediaCollection('photos');
                    $orderedIds[] = $media->id;
                }
            }
        }

        // Reihenfolge offiziell speichern
        Media::setNewOrder($orderedIds);

        // Max 3 Bilder erzwingen
        $catched->getMedia('photos')
            ->sortBy('order_column')
            ->slice(3)
            ->each
            ->delete();

        return redirect()->route('app.catched.show', $catched->id)
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
