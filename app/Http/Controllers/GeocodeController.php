<?php

namespace App\Http\Controllers;

use App\Services\ReverseGeocoder;
use Illuminate\Http\Request;

class GeocodeController extends Controller
{
    public function reverse(Request $request, ReverseGeocoder $geocoder)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json(['address' => $geocoder->address((float) $data['lat'], (float) $data['lng'])]);
    }
}
