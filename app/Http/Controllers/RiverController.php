<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\River;
use App\Models\State;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RiverController extends Controller
{
    /**
     * Zeige einen einzelnen Fluss
     */
    public function show(State $state, River $river)
    {

        abort_unless($river->isVisibleTo(Auth::user()), 404);

        $river->load(['fish' => fn($q) => $q->approved()]);

        return Inertia::render('Rivers/Show', [
            'state_id' => $state->id,
            'river' => [
                'id' => $river->id,
                'name' => $river->name,
                'desc' => $river->desc,
                'hint' => $river->hint,
                'fishing_rights' => $river->fishing_rights,
                'ticket_sales' => $river->ticket_sales,
                'fish' => $river->fish->map(fn($fish) => [
                    'id' => $fish->id,
                    'name' => $fish->name,
                ]),
            ]
        ]);
    }
}
