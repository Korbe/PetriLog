<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class PrivacyPolicyControllerTest extends TestCase
{
    public function test_privacy_policy_page_sets_complete_meta_content_and_renders_view()
    {
        $response = $this->get(route('public.policy'));

        // 🔹 Status OK
        $response->assertStatus(200);

        // 🔹 Meta existiert
        $this->assertTrue(session()->has('meta'));

        $meta = session('meta');

        // 🔹 Standard Meta
        $this->assertEquals(
            'Datenschutzerklärung - PetriLog',
            $meta['title']
        );

        $this->assertEquals(
            'Erfahren Sie, wie PetriLog Ihre personenbezogenen Daten schützt. Informationen zu Datenerhebung, Verarbeitung, Sicherheit und Ihren Rechten gemäß DSGVO.',
            $meta['description']
        );

        // 🔹 OpenGraph
        $this->assertEquals('website', $meta['og:type']);
        $this->assertEquals(url()->current(), $meta['og:url']);
        $this->assertEquals('Datenschutzerklärung - PetriLog', $meta['og:title']);
        $this->assertEquals(
            'Transparente Datenschutzerklärung: Alles über Erhebung, Nutzung, Speicherung und Schutz Ihrer Daten bei PetriLog.',
            $meta['og:description']
        );
        $this->assertEquals(asset('logo.png'), $meta['og:image']);
        $this->assertEquals('PetriLog Logo', $meta['og:image:alt']);
        $this->assertEquals(asset('logo.png'), $meta['og:image:fallback']);

        // 🔹 Twitter
        $this->assertEquals('summary_large_image', $meta['twitter:card']);
        $this->assertEquals(url()->current(), $meta['twitter:url']);
        $this->assertEquals('Datenschutzerklärung - PetriLog', $meta['twitter:title']);
        $this->assertEquals(
            'Erfahren Sie, wie PetriLog mit Ihren personenbezogenen Daten umgeht und diese schützt.',
            $meta['twitter:description']
        );
        $this->assertEquals(asset('logo.png'), $meta['twitter:image']);
        $this->assertEquals(asset('logo.png'), $meta['twitter:image:fallback']);

        // 🔹 Inertia View
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Public/Legal/PrivacyPolicy')
        );
    }
}
