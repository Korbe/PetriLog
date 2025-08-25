<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class TermsOfServiceController extends Controller
{
    public function show()
    {
        return Inertia::render('Public/Legal/TermsOfService', []);
    }
}
