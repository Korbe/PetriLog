<?php

namespace Tests\Feature\Controller\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\State;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_user_admin_routes(): void
    {
        $this->get(route('admin.user.index'))->assertRedirect('/login');
    }

    public function test_user_cannot_access_user_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.user.index'))->assertRedirect('/dashboard');
    }

    public function test_admin_sees_users_with_full_subscription_and_state_data(): void
    {
        // 🔹 Admin
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // 🔹 State
        $state = State::factory()->create([
            'name' => 'Bayern',
        ]);

        // 🔹 Normaler User
        $user = User::factory()->create([
            'name' => 'Max Mustermann',
            'email' => 'max@test.de',
            'state_id' => $state->id,
            'is_admin' => false,
        ]);

        // 🔹 Subscription (mit ends_at → gilt als canceled!)
        Subscription::create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_test_123',
            'stripe_status' => 'active',
            'stripe_price' => 'price_test',
            'quantity' => 1,
            'ends_at' => now()->addMonth(),
        ]);

        // 🔹 Request
        $response = $this->actingAs($admin)
            ->get(route('admin.user.index'));

        // 🔹 HTTP OK
        $response->assertStatus(200);

        // 🔹 Inertia Assertions
        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/User/Index')
                ->has('users', 2)

                // 🔸 Admin selbst
                ->has(
                    'users.0',
                    fn(AssertableInertia $u) =>
                    $u->where('id', $admin->id)
                        ->where('name', $admin->name)
                        ->where('email', $admin->email)
                        ->where('subscribed', false)
                        ->where('subscription_expires_at', null)
                        ->where('canceled', false)
                        ->where('state', null)
                        ->where('is_admin', true)
                        ->has('created_at')
                )

                // 🔸 Normaler User mit Abo
                ->has(
                    'users.1',
                    fn(AssertableInertia $u) =>
                    $u->where('id', $user->id)
                        ->where('name', 'Max Mustermann')
                        ->where('email', 'max@test.de')
                        ->where('subscribed', true)
                        ->where(
                            'subscription_expires_at',
                            $user->subscription('default')->ends_at->toISOString()
                        )
                        ->where('canceled', true) // 👈 WICHTIG: ends_at gesetzt = canceled()
                        ->where('state', 'Bayern')
                        ->where('is_admin', false)
                        ->has('created_at')
                )
        );
    }

    public function test_non_admin_user_cannot_access_user_index(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.user.index'));

        $response->assertRedirect(route('app.dashboard'));
    }
}
