<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Config;

class FishController extends Controller
{
    public function index()
    {
        $fish = Config::get('fish');

        return Inertia::render('Fish/Index', [
            'fish' => $fish
        ]);
    }

    public function fish($fishname)
    {
        $fish = Config::get('fish');

        // Schauen, ob das gesuchte Fisch existiert
        if (!array_key_exists($fishname, $fish)) {
            abort(404, 'Fisch nicht gefunden');
        }

        $data = $fish[$fishname];

        return Inertia::render('Fish/Fish', [
            "fish" => $data
        ]);
    }
}
