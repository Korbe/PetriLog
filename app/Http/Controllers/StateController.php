<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\State;
use App\Http\Controllers\Controller;

class StateController extends Controller
{
    /**
     * Zeige alle States
     */
    public function index()
    {
        session()->forget('meta');

        $states = State::withCount(['lakes', 'rivers', 'associations'])
            ->get()
            ->map(fn($state) => [
                'id' => $state->id,
                'name' => $state->name,
                'desc' => $state->desc,
                'lakes_count' => $state->lakes_count,
                'rivers_count' => $state->rivers_count,
                'associations_count' => $state->associations_count,
                'media' => $state->getMedia('state')->map(fn($m) => [
                    'url' => $m->getUrl(),
                ]),
            ]);

        return Inertia::render('States/Index', [
            'states' => $states
        ]);
    }

    public function show(State $state)
    {
        session()->forget('meta');

        // Relations laden
        $state->load(['lakes', 'rivers', 'associations']);

        return Inertia::render('States/Show', [
            'state' => [
                'id' => $state->id,
                'name' => $state->name,
                'desc' => $state->desc,

                'lakes' => $state->lakes->map(fn($lake) => [
                    'id' => $lake->id,
                    'name' => $lake->name,
                ]),

                'rivers' => $state->rivers->map(fn($river) => [
                    'id' => $river->id,
                    'name' => $river->name,
                ]),

                'associations' => $state->associations->map(fn($assoc) => [
                    'id' => $assoc->id,
                    'name' => $assoc->name,
                    'link' => $assoc->link,
                    'desc' => $assoc->desc,
                ]),
            ]
        ]);
    }
}
