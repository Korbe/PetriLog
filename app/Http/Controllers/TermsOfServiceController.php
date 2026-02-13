<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

class TermsOfServiceController extends Controller
{
    public function show()
    {
        session([
            'meta' => [
                'title' => 'Nutzungsbedingungen | PetriLog',
                'description' => 'Lesen Sie die Nutzungsbedingungen von PetriLog. Erfahren Sie alles über Registrierung, Inhalte, Haftung, Datenschutz und die Nutzung der Angelplattform.',

                'og:type' => 'website',
                'og:url' => url()->current(),
                'og:title' => 'Nutzungsbedingungen | PetriLog',
                'og:description' => 'Transparente Nutzungsbedingungen von PetriLog: Regeln zur Nutzung, Kontosicherheit, hochgeladenen Inhalten, Haftung und Datenschutz.',
                'og:image' => asset('logo.png'),

                'twitter:card' => 'summary_large_image',
                'twitter:url' => url()->current(),
                'twitter:title' => 'Nutzungsbedingungen | PetriLog',
                'twitter:description' => 'Alles Wichtige zu den Nutzungsbedingungen von PetriLog - für sichere und faire Nutzung der Plattform.',
                'twitter:image' => asset('logo.png'),
            ]
        ]);

        return Inertia::render('Public/Legal/TermsOfService', []);
    }
}
