<?php

namespace Tests\Feature\Controller\Admin;

use Tests\TestCase;
use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_index_requires_auth_and_admin(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_association_admin_routes(): void
    {
        $this->get(route('admin.index'))->assertRedirect('/login');
    }

    public function test_user_cannot_access_association_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get(route('admin.index'))->assertRedirect('/dashboard');
    }

    public function test_admin_index_shows_page_for_admin()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertStatus(200)
            ->assertInertia(
                fn(AssertableInertia $page) =>
                $page->component('Admin/Index')
                    ->has('auth.user')
            );
    }
}
