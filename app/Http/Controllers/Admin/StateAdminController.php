<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\State;
use Illuminate\Http\Request;
use App\Http\Requests\StateRequest;
use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class StateAdminController extends Controller
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

    public function store(StateRequest $request)
    {
        $data = $request->validated();

        $state = State::create($data);

        if ($request->hasFile('photo')) {
            $state->addMedia($request->file('photo'))->toMediaCollection('state');
        }

        return redirect()
            ->route('admin.state.index')
            ->with('success', 'Bundesland erstellt!');
    }

    public function edit(State $state)
    {
        $state->load('media');

        return Inertia::render('Admin/State/Edit', [
            'state' => $state,
        ]);
    }

    public function update(StateRequest $request, State $state)
    {
        $data = $request->validated();

        $state->update([
            'name' => $data['name'],
            'desc' => $data['desc'] ?? null,
        ]);

        if ($request->hasFile('photo')) {
            $state->clearMediaCollection('state');
            $state->addMedia($request->file('photo'))->toMediaCollection('state');
        }

        return redirect()
            ->route('admin.state.index')
            ->with('success', 'Bundesland aktualisiert!');
    }

    public function destroy(State $state)
    {
        $state->delete();

        return redirect()->route('admin.state.index');
    }

    public function deletePhoto(int $media)
    {
        $media = Media::findOrFail($media);
        $media->delete();

        return redirect()->back()->with('success', 'Bild gelöscht.');
    }
}
