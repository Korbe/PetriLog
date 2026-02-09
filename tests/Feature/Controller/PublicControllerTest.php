<?php

namespace Tests\Feature\Controller;

use App\Models\User;
use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\River;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PublicControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_page_sets_correct_meta(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Public/Home/Index')
        );

        $this->assertEquals('PetriLog - Dein digitales Fangbuch', session('meta.title'));
        $this->assertEquals(
            'Behalte den Überblick über deine Fänge, teile deine Erlebnisse und analysiere deine Angelerfolge mit PetriLog.',
            session('meta.description')
        );
        $this->assertEquals(asset('logo.png'), session('meta.og:image'));
        $this->assertEquals(url('/'), session('meta.og:url'));
        $this->assertEquals('website', session('meta.og:type'));
        $this->assertEquals('summary_large_image', session('meta.twitter:card'));
    }

    public function test_public_pricing_page_sets_correct_meta(): void
    {
        $response = $this->get('/pricing');

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Public/Pricing/Index')
        );

        $this->assertEquals('PetriLog - Preis', session('meta.title'));
        $this->assertStringContainsString('Ein Preis, ein Paket', session('meta.description'));
        $this->assertEquals(asset('logo.png'), session('meta.og:image'));
        $this->assertEquals(url('/pricing'), session('meta.og:url'));
    }

    public function test_public_contact_page_sets_correct_meta(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Public/Contact/Index')
        );

        $this->assertEquals('PetriLog - Kontakt', session('meta.title'));
        $this->assertStringContainsString('Du hast Fragen oder Feedback?', session('meta.description'));
        $this->assertEquals(asset('logo.png'), session('meta.og:image'));
        $this->assertEquals(url('/contact'), session('meta.og:url'));
    }

    public function test_public_show_catched_sets_correct_meta_and_loads_relationships(): void
    {
        // 🔹 Fake Storage für Spatie Media Library
        Storage::fake('public');

        // 🔹 User, Fish, Lake, River
        $user = User::factory()->create(['name' => 'Max Mustermann']);
        $fish = Fish::factory()->create(['name' => 'Forelle']);
        $lake = Lake::factory()->create(['name' => 'Starnberger See']);
        $river = River::factory()->create(['name' => 'Isar']);

        // 🔹 Catched erstellen
        $catched = Catched::factory()->create([
            'user_id' => $user->id,
            'fish_id' => $fish->id,
            'lake_id' => $lake->id,
            'river_id' => $river->id,
            'date' => Carbon::parse('2026-02-08 12:00'),
            'length' => 42,
        ]);

        // 🔹 Media anhängen
        $file = UploadedFile::fake()->image('catch.jpg');
        $catched->addMedia($file)->toMediaCollection('catched', 'public');

        $response = $this->get(route('public.catched.show', $catched->id));

        // 🔹 Status
        $response->assertStatus(200);

        // 🔹 Inertia Props prüfen
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Public/Catch/Show')
                ->where('user', 'Max Mustermann')
                ->where('catched.id', $catched->id)
                ->where('catched.length', 42)
                ->where('catched.date', $catched->date->toISOString())
                ->has('catched.fish')
                ->has('catched.lake')
                ->has('catched.river')
                ->has('catched.media', 1)
                ->where('catched.media.0.original_url', $catched->getFirstMediaUrl('catched', 'public'))
        );

        // 🔹 Session meta prüfen
        $catchDate = $catched->date->format('d.m.Y H:i');

        $this->assertEquals("Schau was ich gefangen habe - PetriLog", session('meta.title'));
        $this->assertEquals("Gefangen am {$catchDate} mit einer Länge von 42cm.", session('meta.description'));
        $this->assertEquals(route('public.catched.show', $catched->id), session('meta.og:url'));
        $this->assertEquals($catched->getFirstMediaUrl('catched', 'public'), session('meta.og:image'));
        $this->assertEquals('website', session('meta.og:type'));
        $this->assertEquals("Schau was ich gefangen habe - PetriLog", session('meta.og:title'));
        $this->assertEquals("Gefangen am {$catchDate} mit einer Länge von 42cm.", session('meta.og:description'));

        $this->assertEquals("summary_large_image", session('meta.twitter:card'));
        $this->assertEquals("Schau was ich gefangen habe - PetriLog", session('meta.twitter:title'));
        $this->assertEquals("Gefangen am {$catchDate} mit einer Länge von 42cm.", session('meta.twitter:description'));
        $this->assertEquals($catched->getFirstMediaUrl('catched', 'public'), session('meta.twitter:image'));
    }
}
