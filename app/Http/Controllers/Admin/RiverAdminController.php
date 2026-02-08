<?php

namespace App\Http\Controllers\Admin;

use App\Models\Fish;
use Inertia\Inertia;
use App\Models\River;
use App\Models\State;
use App\Http\Requests\RiverRequest;
use App\Http\Controllers\Controller;

class RiverAdminController extends Controller
{
    public function index()
    {
        $rivers = River::with(['fish', 'states'])
            ->get()
            ->sortBy(function ($river) {
                // Sortiere nach dem ersten State alphabetisch (falls mehrere)
                return optional($river->states->first())->name;
            })
            ->values(); // Index neu setzen, weil sortBy Collection zurückgibt

        return Inertia::render('Admin/River/Index', [
            'rivers' => $rivers,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/River/Create', [
            'states' => State::orderBy('name', 'asc')->get(),
            'fish'   => Fish::orderBy('name', 'asc')->get(),
        ]);
    }

    public function store(RiverRequest $request)
    {
        $data = $request->validated();
        $river = River::create($data);

        $river->states()->sync($data['states'] ?? []);
        $river->fish()->sync($data['fish'] ?? []);

        return redirect()->route('admin.river.index')->with('success', 'Fluss erstellt!');
    }

    public function edit(River $river)
    {
        return Inertia::render('Admin/River/Edit', [
            'river'  => $river->load(['states', 'fish']),
            'states' => State::orderBy('name', 'asc')->get(),
            'fish'   => Fish::orderBy('name', 'asc')->get(),
        ]);
    }

    public function update(RiverRequest $request, River $river)
    {
        $data = $request->validated();
        $river->update($data);

        $river->states()->sync($data['states'] ?? []);
        $river->fish()->sync($data['fish'] ?? []);

        return redirect()
            ->route('admin.river.index')
            ->with('success', 'Fluss aktualisiert!');
    }

    public function destroy(River $river)
    {
        $river->delete();
        return redirect()->route('admin.river.index')->with('success', 'Fluss gelöscht!');
    }
}
