<?php

namespace App\Http\Controllers;

use App\Models\Lake;
use Inertia\Inertia;
use App\Models\State;

class LakeController extends Controller
{
    /**
     * Zeige einen einzelnen See inklusive seiner Fische
     */
    public function show(State $state, Lake $lake)
    {
        session()->forget('meta');

        // Lade die Fische für diesen See
        $lake->load('fish');

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
