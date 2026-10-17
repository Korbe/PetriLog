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
        $states = State::with('media')
            ->withCount([
                'lakes' => fn($q) => $q->approved(),
                'rivers' => fn($q) => $q->approved(),
                'associations',
                'users',
            ])
            ->orderBy('name')
            ->get()
            ->map(fn(State $state) => [
                'id' => $state->id,
                'name' => $state->name,
                'image' => $state->getFirstMediaUrl('state') ?: null,
                'has_desc' => filled(strip_tags((string) $state->desc)),
                'lakes_count' => $state->lakes_count,
                'rivers_count' => $state->rivers_count,
                'associations_count' => $state->associations_count,
                'users_count' => $state->users_count,
                'updated_at' => $state->updated_at,
            ]);

        return Inertia::render('Admin/State/Index', [
            'states' => $states,
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
