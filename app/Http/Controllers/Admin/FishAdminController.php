<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fish;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FishAdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Fish/Index', [
            'fishs' => Fish::withCount(['lakes', 'rivers'])->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Fish/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:fish,name',
            'desc' => 'nullable|string',
        ]);

        Fish::create($data);

        return redirect()->route('admin.fish.index')->with('success', 'Fish erstellt!');
    }

    public function edit(Fish $fish)
    {
        return Inertia::render('Admin/Fish/Edit', [
            'fish' => $fish,
        ]);
    }

    public function update(Request $request, Fish $fish)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:fish,name,' . $fish->id,
            'desc' => 'nullable|string',
        ]);

        $fish->update($data);

        return redirect()->route('admin.fish.index')->with('success', 'Fisch aktualisiert!');
    }

    public function destroy(Fish $fish)
    {
        $fish->delete();
        return redirect()->route('admin.fish.index')->with('success', 'Fisch gelöscht!');
    }
}
