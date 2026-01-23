<?php

namespace App\Http\Controllers;

use App\Models\Fish;
use Inertia\Inertia;
use Illuminate\Support\Facades\Config;

class FishController extends Controller
{
    public function index()
    {
        session()->forget('meta');

        $fish = Fish::where('name', '!=', 'Anderes / nicht gelistet')->get()->map(fn($f) => [
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
        session()->forget('meta');

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
