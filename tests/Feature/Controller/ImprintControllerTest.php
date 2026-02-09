<?php

namespace Tests\Feature\Controller;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ImprintControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_imprint_page_sets_meta_session_and_renders_inertia(): void
    {
        $response = $this->get(route('public.imprint'));

        $response->assertStatus(200);

        // Session-Check
        $response->assertSessionHas(
            'meta',
            fn($meta) =>
            $meta['title'] === 'Impressum - PetriLog' &&
                $meta['description'] === 'Impressum der PetriLog App - Angaben gemäß §5 TMG. Kontaktinformationen und rechtliche Hinweise.' &&
                $meta['robots'] === 'index, follow' &&
                $meta['og:title'] === 'Impressum - PetriLog' &&
                $meta['og:description'] === 'Impressum der PetriLog App - Angaben gemäß §5 TMG. Kontaktinformationen und rechtliche Hinweise.' &&
                $meta['twitter:card'] === 'summary_large_image'
        );

        // Inertia-Check
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Public/Legal/Imprint')
                ->etc()
        );
    }
}
