<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\State;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
            'photo'     => 'nullable|image|max:102400',
        ]);

        $state = State::create($data);

        if ($request->hasFile('photo')) {
            $state
                ->addMedia($request->file('photo'))
                ->toMediaCollection('state');
        }

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
        $state->load('media');

        return Inertia::render('Admin/State/Edit', [
            'state' => $state,
        ]);
    }

    public function update(Request $request, State $state)
    {
        $data = $request->validate([
            'name'      => 'required|string|unique:states,name,' . $state->id,
            'desc'      => 'nullable|string',
            'photo'     => 'nullable|image|max:102400',
        ]);

        $state->update([
            'name' => $data['name'],
            'desc' => $data['desc'] ?? null,
        ]);

        if ($request->hasFile('photo')) {
            // altes Bild löschen
            $state->clearMediaCollection('state');

            // neues Bild speichern
            $state
                ->addMedia($request->file('photo'))
                ->toMediaCollection('state');
        }

        return redirect()->route('admin.state.index');
    }

    public function destroy(State $state)
    {
        $state->delete();

        return redirect()->route('admin.state.index');
    }

    public function deletePhoto(Request $request, $mediaId)
    {
        $media = Media::findOrFail($mediaId);

        $media->delete();

        return redirect()->back()->with('success', 'Bild gelöscht.');
    }
}
