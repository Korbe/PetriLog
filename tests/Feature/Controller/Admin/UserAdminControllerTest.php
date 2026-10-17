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

        // 🔹 Inertia Assertions – Reihenfolge ist „neueste zuerst“, daher nach ID suchen
        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/User/Index')
                ->has('users', 2)
                ->where('users', function ($users) use ($admin, $user) {
                    $users = collect($users)->keyBy('id');
                    $adminRow = $users[$admin->id];
                    $userRow = $users[$user->id];

                    // 🔸 Admin selbst
                    $this->assertSame($admin->email, $adminRow['email']);
                    $this->assertFalse($adminRow['subscribed']);
                    $this->assertNull($adminRow['subscription_expires_at']);
                    $this->assertFalse($adminRow['canceled']);
                    $this->assertSame('free', $adminRow['plan']);
                    $this->assertNull($adminRow['state']);
                    $this->assertTrue($adminRow['is_admin']);

                    // 🔸 Normaler User mit gekündigtem, noch laufendem Abo
                    $this->assertSame('Max Mustermann', $userRow['name']);
                    $this->assertSame('max@test.de', $userRow['email']);
                    $this->assertTrue($userRow['subscribed']);
                    $this->assertSame($user->subscription('default')->ends_at->toISOString(), $userRow['subscription_expires_at']);
                    $this->assertTrue($userRow['canceled']); // ends_at gesetzt = canceled()
                    $this->assertSame('canceled', $userRow['plan']);
                    $this->assertSame('Bayern', $userRow['state']);
                    $this->assertFalse($userRow['is_admin']);
                    $this->assertSame(0, $userRow['catches_count']);
                    $this->assertArrayHasKey('last_active_at', $userRow);

                    return true;
                })
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
