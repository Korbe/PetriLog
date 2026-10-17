<?php

namespace App\Http\Controllers;

use App\Models\Fish;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FishController extends Controller
{
    public function index()
    {

        $fish = Fish::approved()->where('name', '!=', 'Anderes / nicht gelistet')
            ->orderBy('name') // <-- hier nach Namen sortieren
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'media' => $f->getMedia('fish')->map(fn($m) => ['url' => $m->getUrl()]),
            ]);

        return Inertia::render('Fish/Index', [
            'fish' => $fish
        ]);
    }

    public function show(Fish $fish)
    {
        // Vorschläge in Prüfung sind nur für Einreichende und Admins sichtbar
        abort_unless($fish->isVisibleTo(Auth::user()), 404);

        return Inertia::render('Fish/Show', [
            'fish' => [
                'id' => $fish->id,
                'name' => $fish->name,
                'desc' => $fish->desc,
                'media' => $fish->getMedia('fish')->map(fn($m) => [
                    'url' => $m->getUrl(),
                ]),
            ]
        ]);
    }
}
