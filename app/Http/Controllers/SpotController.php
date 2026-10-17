<?php

namespace App\Http\Controllers;

use App\Models\Catched;
use App\Models\Spot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Persönliche Spot-Karte: alle eigenen Fänge plus private Angelplätze.
 */
class SpotController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $catches = $user->catched()
            ->with(['fish', 'lake', 'river', 'media'])
            ->orderByDesc('date')
            ->get()
            ->map(fn(Catched $c) => [
                'id' => $c->id,
                'lat' => (float) $c->latitude,
                'lng' => (float) $c->longitude,
                'date' => $c->date,
                'fishId' => $c->fish_id,
                'fish' => $c->fish?->name ?? 'Unbekannter Fisch',
                'water' => $c->lake?->name ?? $c->river?->name,
                'length' => $c->length,
                'weight' => $c->weight,
                'released' => $c->released,
                'photo' => $c->getFirstMedia('photos')?->getUrl(),
            ]);

        return Inertia::render('Spots/Index', [
            'catches' => $catches,
            'spots' => $user->spots()->latest()->get(['id', 'name', 'note', 'latitude', 'longitude']),
        ]);
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $user->spots()->create($this->validated($request));

        return back()->with('success', 'Spot gespeichert.');
    }

    public function update(Request $request, Spot $spot)
    {
        $this->authorizeSpot($spot);

        $spot->update($this->validated($request));

        return back()->with('success', 'Spot aktualisiert.');
    }

    public function destroy(Spot $spot)
    {
        $this->authorizeSpot($spot);

        $spot->delete();

        return back()->with('success', 'Spot gelöscht.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:80',
            'note' => 'nullable|string|max:2000',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'name.required' => 'Bitte gib dem Spot einen Namen.',
        ]);
    }

    // Spots sind privat – auch Admins sehen nur ihre eigenen
    private function authorizeSpot(Spot $spot): void
    {
        abort_unless($spot->user_id === Auth::id(), 403);
    }
}
