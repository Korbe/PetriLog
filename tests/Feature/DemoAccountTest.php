<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_user_cannot_change_profile_password_or_delete_account(): void
    {
        $demo = User::factory()->create(['email' => User::DEMO_EMAIL, 'name' => 'Demo']);

        $this->actingAs($demo)->from('/user/profile')
            ->put('/user/profile-information', ['name' => 'Gehackt', 'email' => 'x@example.com'])
            ->assertRedirect('/user/profile')
            ->assertSessionHas('error');

        $this->actingAs($demo)->put('/user/password', [
            'current_password' => 'password', 'password' => 'neu12345678', 'password_confirmation' => 'neu12345678',
        ])->assertRedirect();

        $this->actingAs($demo)->delete('/user', ['password' => 'password'])->assertRedirect();
        $this->actingAs($demo)->get('/billing')->assertRedirect();

        $demo->refresh();
        $this->assertSame('Demo', $demo->name);
        $this->assertSame(User::DEMO_EMAIL, $demo->email);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $demo->password));
    }

    public function test_demo_user_can_still_view_the_profile(): void
    {
        $demo = User::factory()->create(['email' => User::DEMO_EMAIL]);

        $this->actingAs($demo)->get('/user/profile')->assertOk();
    }

    public function test_regular_users_can_update_their_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/user/profile-information', ['name' => 'Neuer Name', 'email' => $user->email])
            ->assertSessionHasNoErrors();

        $this->assertSame('Neuer Name', $user->refresh()->name);
    }

    public function test_demo_button_logs_into_the_demo_account_without_password(): void
    {
        $demo = User::factory()->create(['email' => User::DEMO_EMAIL, 'email_verified_at' => now()]);

        $this->post(route('demo.login'))->assertRedirect(route('app.dashboard'));

        $this->assertAuthenticatedAs($demo);
    }

    public function test_demo_login_fails_gracefully_without_demo_account_or_when_disabled(): void
    {
        $this->from('/login')->post(route('demo.login'))->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();

        User::factory()->create(['email' => User::DEMO_EMAIL]);
        config(['app.demo_login' => false]);

        $this->from('/login')->post(route('demo.login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logged_in_users_are_not_switched_to_the_demo(): void
    {
        User::factory()->create(['email' => User::DEMO_EMAIL]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('demo.login'))->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }
}
