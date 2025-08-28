<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Config;

class WatersController extends Controller
{
    public function index()
    {
        session()->forget('meta');

        $states = Config::get('states');

        return Inertia::render('Waters/Index', [
            "states" => $states
        ]);
    }

    public function state(string $state)
    {
        session()->forget('meta');

        $states = Config::get('states');

        // Schauen, ob das gesuchte Bundesland existiert
        if (!array_key_exists($state, $states)) {
            abort(404, 'Bundesland nicht gefunden');
        }

        // Passendes Bundesland holen
        $data = $states[$state];

        return Inertia::render('Waters/State', [
            'state' => $data
        ]);
    }

    public function waters($state, $lakeOrRiver)
    {
        session()->forget('meta');

        $waters = Config::get('waters');

        // Schauen, ob das gesuchte See oder Fluss existiert
        if (!array_key_exists($lakeOrRiver, $waters)) {
            abort(404, 'See oder Fluss nicht gefunden');
        }

        // Passendes Bundesland holen
        $data = $waters[$lakeOrRiver];

        return Inertia::render('Waters/Waters', [
            'waters' => $data
        ]);
    }
}
