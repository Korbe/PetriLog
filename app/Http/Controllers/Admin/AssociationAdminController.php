<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\State;
use App\Models\Association;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AssociationAdminController extends Controller
{
    public function index()
    {
        $associations = Association::with('state')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Association/Index', [
            'associations' => $associations
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Association/Create', [
            'states' => State::orderBy('name', 'asc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:associations,name',
            'desc' => 'nullable|string',
            'link' => 'nullable|string',
            'state_id' => 'required|exists:states,id',
        ]);

        Association::create($data);

        return redirect()->route('admin.association.index')->with('success', 'Verein erstellt!');
    }

    public function edit(Association $association)
    {
        return Inertia::render('Admin/Association/Edit', [
            'association' => $association->load('state'),
            'states' => State::orderBy('name', 'asc')->get(),
        ]);
    }

    public function update(Request $request, Association $association)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:associations,name,' . $association->id,
            'desc' => 'nullable|string',
            'link' => 'nullable|string',
            'state_id' => 'required|exists:states,id',
        ]);

        $association->update($data);

        return redirect()->route('admin.association.index')->with('success', 'Verein aktualisiert!');
    }

    public function destroy(Association $association)
    {
        $association->delete();

        return redirect()->route('admin.association.index')->with('success', 'Verein gelöscht!');
    }
}
