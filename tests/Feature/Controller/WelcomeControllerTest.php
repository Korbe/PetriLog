<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WelcomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_is_rendered_without_any_props(): void
    {
        // 🔹 Erstelle einen User
        $user = User::factory()->create();

        // 🔹 Request als eingeloggter User
        $response = $this->actingAs($user)->get('/welcome');

        // 🔹 Status prüfen
        $response->assertStatus(200);

        // 🔹 Inertia Component prüfen, keine Props erlaubt
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Auth/Welcome')
                ->etc()
        );
    }
}
