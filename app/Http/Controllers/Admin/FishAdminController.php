<?php

namespace App\Http\Controllers\Admin;

use App\Models\Fish;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFishRequest;
use App\Http\Requests\UpdateFishRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FishAdminController extends Controller
{
    public function index()
    {
        $fishs = Fish::withCount(['lakes', 'rivers'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Fish/Index', compact('fishs'));
    }

    public function create()
    {
        return Inertia::render('Admin/Fish/Create');
    }

    public function store(StoreFishRequest $request)
    {
        $fish = Fish::create($request->validated());

        if ($request->hasFile('photo')) {
            $fish->addMedia($request->file('photo'))->toMediaCollection('fish');
        }

        return redirect()
            ->route('admin.fish.index')
            ->with('success', 'Fisch erstellt!');
    }

    public function edit(Fish $fish)
    {
        $fish->load('media');

        return Inertia::render('Admin/Fish/Edit', [
            'fish' => $fish,
        ]);
    }

    public function update(UpdateFishRequest  $request, Fish $fish)
    {
        $fish->update($request->validated());

        if ($request->hasFile('photo')) {
            $fish->clearMediaCollection('fish');
            $fish->addMedia($request->file('photo'))->toMediaCollection('fish');
        }

        return redirect()
            ->route('admin.fish.index')
            ->with('success', 'Fisch aktualisiert!');
    }

    public function destroy(Fish $fish)
    {
        $fish->delete();
        return redirect()->route('admin.fish.index')->with('success', 'Fisch gelöscht!');
    }

    public function deletePhoto(int $media)
    {
        $media = Media::findOrFail($media);
        $media->delete();

        return redirect()->back()->with('success', 'Bild gelöscht.');
    }
}
