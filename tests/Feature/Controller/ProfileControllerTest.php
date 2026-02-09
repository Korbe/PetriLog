<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use App\Models\User;
use App\Models\State;
use Laravel\Fortify\Features;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile_page()
    {
        $response = $this->get(route('app.profile.show'));

        $response->assertRedirect(route('login'));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->put('/user/profile-information', [
            'name' => 'Test Name',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Test Name', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }

    public function test_authenticated_user_can_view_profile_page_with_states_sessions_and_2fa_flag(): void
    {
        // 🔹 Session-Driver auf database setzen
        config(['session.driver' => 'database']);

        // 🔹 User
        $user = User::factory()->create();

        // 🔹 States
        $states = State::factory()->count(3)->create();

        // 🔹 Fake Session in DB eintragen
        DB::table(config('session.table', 'sessions'))->insert([
            'id' => 'test-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'payload' => base64_encode('test'),
            'last_activity' => Carbon::now()->timestamp,
        ]);

        // 🔹 Request als eingeloggter User
        $response = $this->actingAs($user)->get(route('app.profile.show'));

        // 🔹 Status prüfen
        $response->assertStatus(200);

        // 🔹 Inertia Assertions
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Profile/Show')

                // 🔹 States: Anzahl + Struktur prüfen
                ->has(
                    'states',
                    $states->count(),
                    fn(Assert $state) => $state
                        ->has('id')
                        ->has('name')
                )

                // 🔹 Sessions prüfen inkl. Agent
                ->has(
                    'sessions',
                    1,
                    fn(Assert $session) => $session
                        ->has('agent.is_desktop')
                        ->has('agent.platform')
                        ->has('agent.browser')
                        ->has('ip_address')
                        ->has('is_current_device')
                        ->has('last_active')
                )

                // 🔹 Two-Factor Authentication Flag
                ->where(
                    'confirmsTwoFactorAuthentication',
                    Features::optionEnabled(
                        Features::twoFactorAuthentication(),
                        'confirm'
                    )
                )
                ->etc()
        );
    }

    public function test_authenticated_user_can_update_newsletter_preference(): void
    {
        // 🔹 User erstellen
        $user = User::factory()->create([
            'newsletter_opt_out' => false, // initial
        ]);

        // 🔹 Request ausführen
        $response = $this->actingAs($user)->patch(route('app.profile.newsletter-preferences.update'), [
            'newsletter_opt_out' => true,
        ]);

        // 🔹 Response prüfen
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Deine Newsletter-Einstellungen wurden aktualisiert.');

        // 🔹 Datenbank prüfen
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'newsletter_opt_out' => true,
        ]);
    }

    public function test_guest_cannot_update_newsletter_preference(): void
    {
        $response = $this->patch(route('app.profile.newsletter-preferences.update'), [
            'newsletter_opt_out' => true,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_validation_fails_with_invalid_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('app.profile.newsletter-preferences.update'), [
            'newsletter_opt_out' => 'not-a-boolean',
        ]);

        $response->assertSessionHasErrors(['newsletter_opt_out']);
    }

    public function test_authenticated_user_can_update_state(): void
    {
        // 🔹 User erstellen
        $user = User::factory()->create([
            'state_id' => null, // initial
        ]);

        // 🔹 State erstellen
        $state = State::factory()->create();

        // 🔹 Request ausführen
        $response = $this->actingAs($user)->patch(route('app.profile.state.update'), [
            'state_id' => $state->id,
        ]);

        // 🔹 Response prüfen
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Deine Bundesland-Einstellung wurden aktualisiert.');

        // 🔹 Datenbank prüfen
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'state_id' => $state->id,
        ]);
    }

    public function test_guest_cannot_update_state(): void
    {
        $state = State::factory()->create();

        $response = $this->patch(route('app.profile.state.update'), [
            'state_id' => $state->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_validation_fails_with_invalid_state_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('app.profile.state.update'), [
            'state_id' => 999999, // existiert nicht
        ]);

        $response->assertSessionHasErrors(['state_id']);
    }
}
