<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class ManifestController extends Controller
{
    public function index()
    {
        return Cache::remember('pwa-manifest', 3600, function () {
            $pwa = config('pwa');
            return response()->json($pwa);
        });
    }
}
