<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Inertia\Inertia;
use App\Models\Catched;
use App\Http\Controllers\Controller;

class PublicController extends Controller
{
    private const FAQS = [
        [
            'question' => 'Was ist PetriLog?',
            'answer' => 'PetriLog ist dein digitales Angel-Tagebuch in Österreich, in dem du alle gefangenen Fische mit Länge, Gewicht, Tiefe, Wassertemperatur, Fotos und mehr festhalten kannst.',
        ],
        [
            'question' => 'Kann ich mehrere Fänge pro Tag eintragen?',
            'answer' => 'Ja! Du kannst mit PetriLog Plus unbegrenzt viele Einträge pro Tag hinzufügen und behältst so den vollen Überblick über deine Fänge. Ohne Abo sind die Einträge auf maximal 5 begrenzt.',
        ],
        [
            'question' => 'Wie viele Fotos kann ich pro Fang speichern?',
            'answer' => 'Du kannst bis zu 3 Bilder pro Fang hinzufügen, um deinen größten Erfolg visuell festzuhalten.',
        ],
        [
            'question' => 'Kann ich meine Fänge mit Freunden teilen?',
            'answer' => 'Ja! Du kannst einzelne Fänge über Social Media oder einen direkten Link teilen.',
        ],
        [
            'question' => 'Sind meine Daten sicher gespeichert?',
            'answer' => 'Alle deine Daten werden in der Cloud gespeichert und sind jederzeit von all deinen Geräten aus zugänglich.',
        ],
        [
            'question' => 'Gibt es Statistiken und Analysen?',
            'answer' => 'Ja! PetriLog zeigt dir Fangstatistiken nach Tag, Monat und Jahr, inklusive Diagrammen und Gesamtübersichten.',
        ],
        [
            'question' => 'Funktioniert PetriLog auf dem Smartphone?',
            'answer' => 'Ja! PetriLog ist für mobile Geräte optimiert, sodass du unterwegs direkt Fänge eintragen und nachschauen kannst. Du kannst PetriLog auch wie eine App auf deinem Startbildschirm installieren.',
        ],
        [
            'question' => 'Wie kann ich PetriLog abonnieren?',
            'answer' => 'Wenn du eingeloggt bist, gehe auf den Reiter „Abo“ und abonniere dort den Jahresplan.',
        ],
        [
            'question' => 'Gibt es ein Monatsabo?',
            'answer' => 'Nein, es gibt nur ein Jahresabo, das du buchen kannst.',
        ],
        [
            'question' => 'Habt ihr auch deutsche Seen?',
            'answer' => 'PetriLog bietet derzeit nur österreichische Gewässer an.',
        ],
    ];

    public function index()
    {
        return Inertia::render('Public/Home/Index')->withViewData([
            'seo' => Seo::page(
                'Das digitale Fangbuch für Angler in Österreich',
                'Halte jeden Fang fest: Länge, Gewicht, Köder, Tiefe, Wassertemperatur, GPS-Spot und bis zu 3 Fotos. '
                    . 'Das private Fangbuch für Österreichs Angler – kostenlos starten.',
                ['jsonLd' => [Seo::webApplication()]],
            ),
        ]);
    }

    public function pricing()
    {
        return Inertia::render('Public/Pricing/Index', [
            'faqs' => self::FAQS,
        ])->withViewData([
            'seo' => Seo::page(
                'Preise',
                'Starte kostenlos mit bis zu 5 Fängen. PetriLog Plus: unbegrenzte Fänge, Fotos, Statistiken und '
                    . 'GPS-Spots für 49,99 € im Jahr inkl. MwSt.',
                ['jsonLd' => [Seo::faqPage(self::FAQS)], 'breadcrumb' => true],
            ),
        ]);
    }

    public function contact()
    {
        return Inertia::render('Public/Contact/Index')->withViewData([
            'seo' => Seo::page(
                'Kontakt',
                'Fragen, Feedback oder dein Gewässer fehlt? Erreiche das PetriLog-Team per E-Mail an '
                    . 'info@petrilog.com oder telefonisch.',
                ['breadcrumb' => true],
            ),
        ]);
    }

    public function showCatched(Catched $catched)
    {
        $catchDate = $catched->date->format('d.m.Y');
        $fishName = $catched->fish?->name ?? 'Fang';
        $description = "{$fishName}, gefangen am {$catchDate}"
            . ($catched->length ? " mit einer Länge von {$catched->length} cm" : '') . '. Festgehalten mit PetriLog.';

        return Inertia::render('Public/Catch/Show', [
            'catched' => $catched->load(['media', 'fish', 'lake', 'river']),
            'user' => $catched->user->name,
        ])->withViewData([
            // Shared catches are reachable by link, but should not show up in search results.
            'seo' => Seo::page("{$fishName} – schau, was ich gefangen habe", $description, [
                'robots' => 'noindex, follow',
                'canonical' => route('public.catched.show', $catched->id),
                'image' => optional($catched->media->first())->original_url,
                'imageAlt' => $fishName,
                'type' => 'article',
            ]),
        ]);
    }
}
