<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\River;
use App\Models\State;
use App\Models\Fish;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RiverController extends Controller
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
            'states' => State::all(),
            'fish'   => Fish::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string',
            'desc'      => 'nullable|string',
            'hint'      => 'nullable|string',
            'states'    => 'required|array',
            'states.*'  => 'exists:states,id',
            'fish'      => 'array',
            'fish.*'    => 'exists:fish,id',
        ]);

        $river = River::create($data);
        $river->states()->sync($data['states'] ?? []);
        $river->fish()->sync($data['fish'] ?? []);

        return redirect()->route('admin.river.index')->with('success', 'Fluss erstellt!');
    }

    public function show(River $river)
    {
        return Inertia::render('Admin/River/Show', [
            'river' => $river->load(['states', 'fish']),
        ]);
    }

    public function edit(River $river)
    {
        return Inertia::render('Admin/River/Edit', [
            'river'  => $river->load(['states', 'fish']),
            'states' => State::all(),
            'fish'   => Fish::all(),
        ]);
    }

    public function update(Request $request, River $river)
    {
        $data = $request->validate([
            'name'      => 'required|string',
            'desc'      => 'nullable|string',
            'hint'      => 'nullable|string',
            'states'    => 'required|array',
            'states.*'  => 'exists:states,id',
            'fish'      => 'array',
            'fish.*'    => 'exists:fish,id',
        ]);

        $river->update([
            'name' => $data['name'],
            'desc' => $data['desc'] ?? null,
            'hint' => $data['hint'] ?? null,
        ]);

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
