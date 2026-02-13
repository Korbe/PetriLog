<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\State;
use App\Models\Association;
use App\Http\Controllers\Controller;

class AssociationController extends Controller
{
    /**
     * Zeige einen einzelnen Verein an
     */
    public function show(State $state, Association $association)
    {
        session()->forget('meta');

        return Inertia::render('Association/Show', [
            'state_id' => $state->id,
            'association' => [
                'id' => $association->id,
                'name' => $association->name,
                'desc' => $association->desc,
                'link' => $association->link,
            ]
        ]);
    }
}
