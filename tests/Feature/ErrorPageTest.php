<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_page_renders_branded_error_page_with_navigation(): void
    {
        $this->get('/gibt-es-nicht')
            ->assertNotFound()
            ->assertInertia(fn(Assert $page) => $page->component('Public/Error')->where('status', 404)->where('auth.user', null));
    }

    public function test_missing_catch_shows_error_page_for_logged_in_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/catched/999999/edit')
            ->assertNotFound()
            ->assertInertia(fn(Assert $page) => $page->component('Public/Error')->where('auth.user.id', $user->id));
    }

    public function test_inertia_visits_receive_the_error_page_as_inertia_response(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/catched/999999/edit', ['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->assertNotFound()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Public/Error');
    }

    public function test_json_requests_keep_json_errors(): void
    {
        $this->getJson('/gibt-es-nicht')->assertNotFound()->assertJsonStructure(['message']);
    }
}
