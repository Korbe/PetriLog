<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lake;
use App\Models\State;
use App\Models\Fish;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LakeController extends Controller
{
    public function index()
    {
        $lake = Lake::with(['fish', 'states'])
            ->get()
            ->sortBy(function ($lake) {
                // Sortiere nach dem ersten State alphabetisch (falls mehrere)
                return optional($lake->states->first())->name;
            })
            ->values(); // Index neu setzen, weil sortBy Collection zurückgibt

        return Inertia::render('Admin/Lake/Index', [
            'lakes' => $lake,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Lake/Create', [
            'states' => State::all(),
            'fish'   => Fish::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string',
            'desc'     => 'nullable|string',
            'hint'     => 'nullable|string',
            'states'    => 'required|array',
            'states.*'  => 'exists:states,id',
            'fish'      => 'array',
            'fish.*'    => 'exists:fish,id',
        ]);

        $lake = Lake::create($data);
        $lake->states()->sync($data['states'] ?? []);
        $lake->fish()->sync($data['fish'] ?? []);


        return redirect()->route('admin.lake.index')->with('success', 'See erstellt!');
    }

    public function show(Lake $lake)
    {
        return Inertia::render('Admin/Lake/Show', [
            'lake' => $lake->load(['state', 'fish']),
        ]);
    }

    public function edit(Lake $lake)
    {
        return Inertia::render('Admin/Lake/Edit', [
            'lake'   => $lake->load('states','fish'),
            'states' => State::all(),
            'fish'   => Fish::all(),
        ]);
    }

    public function update(Request $request, Lake $lake)
    {
        $data = $request->validate([
            'name'     => 'required|string',
            'desc'     => 'nullable|string',
            'hint'     => 'nullable|string',
            'states'    => 'required|array',
            'states.*'  => 'exists:states,id',
            'fish'      => 'array',
            'fish.*'    => 'exists:fish,id',
        ]);

        $lake->update([
            'name' => $data['name'],
            'desc' => $data['desc'] ?? null,
            'hint' => $data['hint'] ?? null,
        ]);

        $lake->states()->sync($data['states'] ?? []);
        $lake->fish()->sync($data['fish'] ?? []);

        return redirect()->route('admin.lake.index')->with('success', 'See aktualisiert!');
    }

    public function destroy(Lake $lake)
    {
        $lake->delete();
        return redirect()->route('admin.lake.index')->with('success', 'See gelöscht!');
    }
}
