<?php

namespace App\Http\Controllers;

use App\Models\Lake;
use Inertia\Inertia;
use App\Models\State;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LakeController extends Controller
{
    /**
     * Zeige einen einzelnen See inklusive seiner Fische
     */
    public function show(State $state, Lake $lake)
    {

        // Lade die Fische für diesen See
        abort_unless($lake->isVisibleTo(Auth::user()), 404);

        $lake->load(['fish' => fn($q) => $q->approved()]);

        return Inertia::render('Lakes/Show', [
            'state_id' => $state->id,
            'lake' => [
                'id' => $lake->id,
                'name' => $lake->name,
                'desc' => $lake->desc,
                'hint' => $lake->hint,
                'fishing_rights' => $lake->fishing_rights,
                'ticket_sales' => $lake->ticket_sales,
                'fish' => $lake->fish->map(fn($fish) => [
                    'id' => $fish->id,
                    'name' => $fish->name,
                ]),
            ]
        ]);
    }
}
