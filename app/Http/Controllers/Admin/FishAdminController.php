<?php

namespace App\Http\Controllers\Admin;

use App\Models\Fish;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
            'name'  => 'required|string|unique:fish,name',
            'desc'  => 'nullable|string',
            'photo' => 'nullable|image|max:102400',
        ]);

        $fish = Fish::create([
            'name' => $data['name'],
            'desc' => $data['desc'] ?? null,
        ]);

        if ($request->hasFile('photo')) {
            $fish
                ->addMedia($request->file('photo'))
                ->toMediaCollection('fish');
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

    public function update(Request $request, Fish $fish)
    {
        $data = $request->validate([
            'name'  => 'required|string|unique:fish,name,' . $fish->id,
            'desc'  => 'nullable|string',
            'photo' => 'nullable|image|max:102400',
        ]);

        $fish->update([
            'name' => $data['name'],
            'desc' => $data['desc'] ?? null,
        ]);

        if ($request->hasFile('photo')) {
            // altes Bild löschen
            $fish->clearMediaCollection('fish');

            // neues Bild speichern
            $fish
                ->addMedia($request->file('photo'))
                ->toMediaCollection('fish');
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

    public function deletePhoto(Request $request, $mediaId)
    {
        $media = Media::findOrFail($mediaId);

        $media->delete();

        return redirect()->back()->with('success', 'Bild gelöscht.');
    }
}
