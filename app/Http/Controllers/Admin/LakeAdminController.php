<?php

namespace App\Http\Controllers\Admin;

use App\Models\Fish;
use App\Models\Lake;
use Inertia\Inertia;
use App\Models\State;
use App\Http\Controllers\Controller;
use App\Http\Requests\LakeRequest;

class LakeAdminController extends Controller
{
    public function index()
    {
        $lake = Lake::with(['fish', 'states'])
            ->get()
            ->sortBy('name')
            ->values(); // Index neu setzen, weil sortBy Collection zurückgibt

        return Inertia::render('Admin/Lake/Index', [
            'lakes' => $lake,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Lake/Create', [
            'states' => State::orderBy('name', 'asc')->get(),
            'fish'   => Fish::orderBy('name', 'asc')->get(),
        ]);
    }

    public function store(LakeRequest $request)
    {
        $data = $request->validated();

        $lake = Lake::create($data);
        $lake->states()->sync($data['states'] ?? []);
        $lake->fish()->sync($data['fish'] ?? []);

        return redirect()->route('admin.lake.index')->with('success', 'See erstellt!');
    }

    public function edit(Lake $lake)
    {
        return Inertia::render('Admin/Lake/Edit', [
            'lake'   => $lake->load('states', 'fish'),
            'states' => State::orderBy('name', 'asc')->get(),
            'fish'   => Fish::orderBy('name', 'asc')->get(),
        ]);
    }

    public function update(LakeRequest $request, Lake $lake)
    {
        $data = $request->validated();

        $lake->update($data);

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
