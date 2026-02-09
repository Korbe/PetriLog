<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PwaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_pwa_index_without_props(): void
    {
        // 🔹 Erstelle einen User
        $user = User::factory()->create();

        // 🔹 Request als eingeloggter User
        $response = $this->actingAs($user)->get(route('app.pwa.index'));

        // 🔹 Status prüfen
        $response->assertStatus(200);

        // 🔹 Inertia-Component prüfen
        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Pwa/Index')
                ->hasAll([]) // Keine Props
        );
    }

    public function test_guest_cannot_access_pwa_index(): void
    {
        $response = $this->get(route('app.pwa.index'));

        // 🔹 Gast sollte auf Login weitergeleitet werden
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
