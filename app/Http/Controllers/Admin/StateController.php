<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\State;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StateController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/State/Index', [
            'states' => State::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/State/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|unique:states,name',
            'desc'      => 'nullable|string',
        ]);

        State::create($data);

        return redirect()->route('admin.state.index');
    }

    public function show(State $state)
    {
        return Inertia::render('Admin/State/Show', [
            'state' => $state->load(['lakes.fish', 'rivers']),
        ]);
    }

    public function edit(State $state)
    {
        return Inertia::render('Admin/State/Edit', [
            'state' => $state,
        ]);
    }

    public function update(Request $request, State $state)
    {
        $data = $request->validate([
            'name'      => 'required|string|unique:states,name,' . $state->id,
            'desc'      => 'nullable|string',
        ]);

        $state->update($data);

        return redirect()->route('admin.state.index');
    }

    public function destroy(State $state)
    {
        $state->delete();

        return redirect()->route('admin.state.index');
    }
}
