<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

class PrivacyPolicyController extends Controller
{
    public function show()
    {
        session([
            'meta' => [
                'title' => 'Datenschutzerklärung - PetriLog',
                'description' => 'Erfahren Sie, wie PetriLog Ihre personenbezogenen Daten schützt. Informationen zu Datenerhebung, Verarbeitung, Sicherheit und Ihren Rechten gemäß DSGVO.',

                'og:type' => 'website',
                'og:url' => url()->current(),
                'og:title' => 'Datenschutzerklärung - PetriLog',
                'og:description' => 'Transparente Datenschutzerklärung: Alles über Erhebung, Nutzung, Speicherung und Schutz Ihrer Daten bei PetriLog.',
                'og:image' => asset('logo.png'),
                'og:image:alt' => 'PetriLog Logo',
                'og:image:fallback' => asset('logo.png'),

                'twitter:card' => 'summary_large_image',
                'twitter:url' => url()->current(),
                'twitter:title' => 'Datenschutzerklärung - PetriLog',
                'twitter:description' => 'Erfahren Sie, wie PetriLog mit Ihren personenbezogenen Daten umgeht und diese schützt.',
                'twitter:image' => asset('logo.png'),
                'twitter:image:fallback' => asset('logo.png'),
            ]
        ]);

        return Inertia::render('Public/Legal/PrivacyPolicy', []);
    }
}
