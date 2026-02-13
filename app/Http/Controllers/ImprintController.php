<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

class ImprintController extends Controller
{
    public function show()
    {
        session([
            'meta' => [
                'title' => 'Impressum - PetriLog',
                'description' => 'Impressum der PetriLog App - Angaben gemäß §5 TMG. Kontaktinformationen und rechtliche Hinweise.',
                'robots' => 'index, follow',
                
                'og:title' => 'Impressum - PetriLog',
                'og:description' => 'Impressum der PetriLog App - Angaben gemäß §5 TMG. Kontaktinformationen und rechtliche Hinweise.',
                'og:type' => 'website',
                'og:url' => url()->current(),
                'og:image' => asset('logo.png'),
                
                'twitter:card' => 'summary_large_image',
                'twitter:title' => 'Impressum - PetriLog',
                'twitter:description' => 'Impressum der PetriLog App - Angaben gemäß §5 TMG. Kontaktinformationen und rechtliche Hinweise.',
                'twitter:image' => asset('logo.png'),
            ]
        ]);

        return Inertia::render('Public/Legal/Imprint', []);
    }
}
