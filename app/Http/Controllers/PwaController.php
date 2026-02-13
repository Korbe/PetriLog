<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

class PwaController extends Controller
{
    /**
     * Zeige einen einzelnen Verein an
     */
    public function index()
    {
        return Inertia::render('Pwa/Index', []);
    }
}