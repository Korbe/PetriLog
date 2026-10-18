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

    public function test_public_home_page_sets_seo(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page->component('Public/Home/Index'));

        $seo = $response->viewData('seo');
        $this->assertStringContainsString('Das digitale Fangbuch', $seo['title']);
        $this->assertSame(url('/'), $seo['canonical']);
        $this->assertSame('website', $seo['og']['type']);
    }

    public function test_public_pricing_page_sets_seo(): void
    {
        $response = $this->get('/pricing');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page->component('Public/Pricing/Index')->has('faqs'));

        $this->assertStringStartsWith('Preise', $response->viewData('seo')['title']);
        $this->assertSame(url('/pricing'), $response->viewData('seo')['canonical']);
    }

    public function test_public_contact_page_sets_seo(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page->component('Public/Contact/Index'));

        $this->assertStringStartsWith('Kontakt', $response->viewData('seo')['title']);
    }

    private function catchFor(User $user, array $attributes = []): Catched
    {
        return Catched::factory()->create([
            'user_id' => $user->id,
            'fish_id' => Fish::factory()->create(['name' => 'Hecht'])->id,
            'lake_id' => Lake::factory()->create(['name' => 'Starnberger See'])->id,
            'river_id' => River::factory()->create(['name' => 'Isar'])->id,
            'date' => Carbon::parse('2026-02-08 12:00'),
            'length' => 42,
            ...$attributes,
        ]);
    }

    public function test_shared_catch_is_visible_via_its_token(): void
    {
        Storage::fake('public');

        $catched = $this->catchFor(User::factory()->create(['name' => 'Max Mustermann']));
        $catched->addMedia(UploadedFile::fake()->image('catch.jpg'))->toMediaCollection('photos', 'public');
        $catched->share();

        $response = $this->get($catched->shareUrl());

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Public/Catch/Show')
                ->where('user', 'Max Mustermann')
                ->where('catched.id', $catched->id)
                ->where('catched.length', 42)
                ->missing('catched.share_token')
                ->has('catched.fish')
                ->has('catched.lake')
                ->has('catched.river')
                ->has('catched.media', 1)
        );

        $seo = $response->viewData('seo');
        $this->assertStringStartsWith('Hecht', $seo['title']);
        $this->assertSame('noindex, follow', $seo['robots']);
        $this->assertSame($catched->shareUrl(), $seo['canonical']);
    }

    /**
     * Sicherheit: Fänge dürfen nicht über die fortlaufende ID abrufbar sein (Durchprobieren aller Fänge).
     */
    public function test_catches_cannot_be_enumerated_by_id(): void
    {
        $private = $this->catchFor(User::factory()->create());
        $shared = $this->catchFor(User::factory()->create());
        $shared->share();

        $this->get("/catch/{$private->id}")->assertNotFound();
        $this->get("/catch/{$shared->id}")->assertNotFound();
        $this->get('/catch/' . str_repeat('a', 32))->assertNotFound();
    }

    public function test_owner_can_share_and_revoke_a_catch(): void
    {
        $user = User::factory()->create();
        $catched = $this->catchFor($user);

        $this->actingAs($user)->post(route('app.catched.share', $catched))->assertRedirect();
        $url = $catched->refresh()->shareUrl();
        $this->assertNotNull($url);
        $this->assertNotNull($catched->shared_at);

        // Erneutes Teilen behält den Link
        $this->actingAs($user)->post(route('app.catched.share', $catched));
        $this->assertSame($url, $catched->refresh()->shareUrl());

        $this->get($url)->assertOk();

        $this->actingAs($user)->delete(route('app.catched.unshare', $catched))->assertRedirect();
        $this->assertNull($catched->refresh()->share_token);

        auth()->logout();
        $this->get($url)->assertNotFound();
    }

    public function test_other_users_cannot_share_foreign_catches(): void
    {
        $catched = $this->catchFor(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->post(route('app.catched.share', $catched))
            ->assertForbidden();

        $this->assertNull($catched->refresh()->share_token);
    }
}
