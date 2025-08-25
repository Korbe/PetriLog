<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ImprintController extends Controller
{
    public function show()
    {
        return Inertia::render('Public/Legal/Imprint', []);
    }
}
