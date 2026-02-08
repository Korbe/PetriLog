<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\State;
use App\Models\Association;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssociationRequest;
use App\Http\Requests\UpdateAssociationRequest;

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

    public function store(StoreAssociationRequest $request)
    {
        Association::create($request->validated());

        return redirect()->route('admin.association.index')->with('success', 'Verein erstellt!');
    }

    public function edit(Association $association)
    {
        return Inertia::render('Admin/Association/Edit', [
            'association' => $association->load('state'),
            'states' => State::orderBy('name', 'asc')->get(),
        ]);
    }

    public function update(UpdateAssociationRequest $request, Association $association)
    {
        $association->update($request->validated());

        return redirect()->route('admin.association.index')->with('success', 'Verein aktualisiert!');
    }

    public function destroy(Association $association)
    {
        $association->delete();

        return redirect()->route('admin.association.index')->with('success', 'Verein gelöscht!');
    }
}
