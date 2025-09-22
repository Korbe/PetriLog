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

        $lakes = Config::get('lakes');
        $rivers = Config::get('rivers');

        // Schauen, ob das gesuchte See oder Fluss existiert
        if (array_key_exists($lakeOrRiver, $lakes)) {
            $data = $lakes[$lakeOrRiver];
        }else if(array_key_exists($lakeOrRiver, $rivers)){
            $data = $rivers[$lakeOrRiver];
        }else
            abort(404, 'See oder Fluss nicht gefunden');

        return Inertia::render('Waters/Waters', [
            'waters' => $data
        ]);
    }
}
