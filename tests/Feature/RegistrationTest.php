<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\State;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    // public function test_registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    // {
    //     if (Features::enabled(Features::registration())) {
    //         $this->markTestSkipped('Registration support is enabled.');
    //     }

    //     $response = $this->get('/register');

    //     $response->assertStatus(404);
    // }

    public function test_new_users_can_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('app.welcome', absolute: false));
    }

    public function test_new_users_can_register_with_state(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $state = \App\Models\State::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'state_id' => $state->id,
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('app.welcome', absolute: false));
    }
}
