<?php

namespace Tests\Feature\Controller;

use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class TermsOfServiceControllerTest extends TestCase
{
    public function test_public_terms_of_service_page_sets_correct_meta(): void
    {
        $response = $this->get(route('public.terms'));

        // 🔹 Status prüfen
        $response->assertStatus(200);

        // 🔹 Inertia-Komponente prüfen (keine eigenen Props)
        $response->assertInertia(
            fn(AssertableInertia $page) => $page
                ->component('Public/Legal/TermsOfService')
                ->etc() // globale Props ignorieren
        );

        // 🔹 Session meta prüfen
        $this->assertEquals('Nutzungsbedingungen | PetriLog', session('meta.title'));
        $this->assertEquals(
            'Lesen Sie die Nutzungsbedingungen von PetriLog. Erfahren Sie alles über Registrierung, Inhalte, Haftung, Datenschutz und die Nutzung der Angelplattform.',
            session('meta.description')
        );

        $this->assertEquals('website', session('meta.og:type'));
        $this->assertEquals(url()->current(), session('meta.og:url'));
        $this->assertEquals('Nutzungsbedingungen | PetriLog', session('meta.og:title'));
        $this->assertEquals(
            'Transparente Nutzungsbedingungen von PetriLog: Regeln zur Nutzung, Kontosicherheit, hochgeladenen Inhalten, Haftung und Datenschutz.',
            session('meta.og:description')
        );
        $this->assertEquals(asset('logo.png'), session('meta.og:image'));

        $this->assertEquals('summary_large_image', session('meta.twitter:card'));
        $this->assertEquals(url()->current(), session('meta.twitter:url'));
        $this->assertEquals('Nutzungsbedingungen | PetriLog', session('meta.twitter:title'));
        $this->assertEquals(
            'Alles Wichtige zu den Nutzungsbedingungen von PetriLog - für sichere und faire Nutzung der Plattform.',
            session('meta.twitter:description')
        );
        $this->assertEquals(asset('logo.png'), session('meta.twitter:image'));
    }
}
