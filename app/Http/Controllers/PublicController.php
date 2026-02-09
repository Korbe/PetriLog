<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Catched;

class PublicController extends Controller
{
    public function index()
    {
        session([
            'meta' => [
                'title' => 'PetriLog - Dein digitales Fangbuch',
                'description' => 'Behalte den Überblick über deine Fänge, teile deine Erlebnisse und analysiere deine Angelerfolge mit PetriLog.',

                'og:title' => 'PetriLog - Dein digitales Fangbuch',
                'og:description' => 'Behalte den Überblick über deine Fänge, teile deine Erlebnisse und analysiere deine Angelerfolge mit PetriLog.',
                'og:image' => asset('logo.png'),
                'og:url' => url()->current(),
                'og:type' => 'website',

                'twitter:card' => 'summary_large_image',
                'twitter:url' => url()->current(),
                'twitter:title' => 'PetriLog - Dein digitales Fangbuch',
                'twitter:description' => 'Behalte den Überblick über deine Fänge, teile deine Erlebnisse und analysiere deine Angelerfolge mit PetriLog.',
                'twitter:image' => asset('logo.png'),
            ],
        ]);

        return Inertia::render('Public/Home/Index');
    }

    public function pricing()
    {
        session([
            'meta' => [
                'title' => 'PetriLog - Preis',
                'description' => 'Ein Preis, ein Paket: Sichere dir das PetriLog Jahresabo und nutze alle Funktionen ohne Einschränkung.',

                'og:title' => 'PetriLog Preis - Jahresabo',
                'og:description' => 'Ein Preis, ein Paket: Sichere dir das PetriLog Jahresabo und nutze alle Funktionen ohne Einschränkung.',
                'og:image' => asset('logo.png'),
                'og:url' => url()->current(),
                'og:type' => 'website',

                'twitter:card' => 'summary_large_image',
                'twitter:url' => url()->current(),
                'twitter:title' => 'PetriLog Preis - Jahresabo',
                'twitter:description' => 'Ein Preis, ein Paket: Sichere dir das PetriLog Jahresabo und nutze alle Funktionen ohne Einschränkung.',
                'twitter:image' => asset('logo.png'),
            ]
        ]);

        return Inertia::render('Public/Pricing/Index');
    }

    public function contact()
    {
        session([
            'meta' => [
                'title' => 'PetriLog - Kontakt',
                'description' => 'Du hast Fragen oder Feedback? Kontaktiere uns direkt - wir freuen uns von dir zu hören.',

                'og:title' => 'PetriLog - Kontakt',
                'og:description' => 'Du hast Fragen oder Feedback? Kontaktiere uns direkt - wir freuen uns von dir zu hören.',
                'og:image' => asset('logo.png'),
                'og:url' => url()->current(),
                'og:type' => 'website',

                'twitter:card' => 'summary_large_image',
                'twitter:url' => url()->current(),
                'twitter:title' => 'PetriLog - Kontakt',
                'twitter:description' => 'Du hast Fragen oder Feedback? Kontaktiere uns direkt - wir freuen uns von dir zu hören.',
                'twitter:image' => asset('logo.png'),
            ]
        ]);

        return Inertia::render('Public/Contact/Index');
    }



    public function showCatched(Catched $catched)
    {
        $userName = $catched->user->name;
        $catchDate = $catched->date->format('d.m.Y H:i');

        session([
            'meta' => [
                'title' => "Schau was ich gefangen habe - PetriLog",
                'description' => "Gefangen am {$catchDate} mit einer Länge von {$catched->length}cm.",
                
                'og:title' => "Schau was ich gefangen habe - PetriLog",
                'og:description' => "Gefangen am {$catchDate} mit einer Länge von {$catched->length}cm.",
                'og:image' => optional($catched->media->first())->original_url ?: asset('logo.png'),
                'og:url' => route('public.catched.show', $catched->id),
                'og:type' => 'website',

                'twitter:card' => 'summary_large_image',
                'twitter:title' => "Schau was ich gefangen habe - PetriLog",
                'twitter:description' => "Gefangen am {$catchDate} mit einer Länge von {$catched->length}cm.",
                'twitter:image' => optional($catched->media->first())->original_url ?: asset('logo.png'),
            ]
        ]);

        return Inertia::render('Public/Catch/Show', [
            'catched' => $catched->load(['media', 'fish', 'lake', 'river']),
            'user' => $userName
        ]);
    }
}
