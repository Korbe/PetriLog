<?php

namespace Tests\Feature\Controller\Admin;

use Tests\TestCase;
use App\Models\Fish;
use App\Models\User;
use App\Models\River;
use App\Models\State;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

class RiverAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_river_admin_routes(): void
    {
        $river = River::factory()->create();

        $this->get(route('admin.river.index'))->assertRedirect('/login');
        $this->get(route('admin.river.create'))->assertRedirect('/login');
        $this->get(route('admin.river.edit', $river))->assertRedirect('/login');
        $this->put(route('admin.river.update', $river), [])->assertRedirect('/login');
        $this->delete(route('admin.river.destroy', $river))->assertRedirect('/login');
    }

    public function test_user_cannot_access_river_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $river = River::factory()->create();

        $this->actingAs($user)->get(route('admin.river.index'))->assertRedirect('/dashboard');
        $this->actingAs($user)->get(route('admin.river.create'))->assertRedirect('/dashboard');
        $this->actingAs($user)->get(route('admin.river.edit', $river))->assertRedirect('/dashboard');
        $this->actingAs($user)->put(route('admin.river.update', $river), [])->assertRedirect('/dashboard');
        $this->actingAs($user)->delete(route('admin.river.destroy', $river))->assertRedirect('/dashboard');
    }

    public function test_admin_can_view_river_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $rivers = River::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('admin.river.index'));
        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/River/Index')
                ->has('rivers', 2)
        );
    }

    public function test_admin_can_view_river_create_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        State::factory()->count(2)->create();
        Fish::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('admin.river.create'));
        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/River/Create')
                ->has('states', 2)
                ->has('fish', 2)
        );
    }

    public function test_admin_can_store_river_with_states_and_fish(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $states = State::factory()->count(2)->create();
        $fish = Fish::factory()->count(2)->create();

        $data = [
            'name' => 'Isar',
            'desc' => 'Schöner Fluss',
            'hint' => 'Nur tagsüber angeln',
            'fishing_rights' => 'Angelschein erforderlich',
            'ticket_sales' => 'Am Kiosk',
            'states' => $states->pluck('id')->toArray(),
            'fish' => $fish->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($admin)->post(route('admin.river.store'), $data);

        $response->assertRedirect(route('admin.river.index'));
        $response->assertSessionHas('success', 'Fluss erstellt!');

        $river = River::first();
        $this->assertEquals('Isar', $river->name);
        $this->assertCount(2, $river->states);
        $this->assertCount(2, $river->fish);
    }

    public function test_admin_can_view_river_edit_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $river = River::factory()->create();
        $states = State::factory()->count(2)->create();
        $fish = Fish::factory()->count(2)->create();

        $river->states()->sync($states->pluck('id'));
        $river->fish()->sync($fish->pluck('id'));

        $response = $this->actingAs($admin)->get(route('admin.river.edit', $river));
        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/River/Edit')
                ->has('river.states', 2)
                ->has('river.fish', 2)
                ->has('states', 2)
                ->has('fish', 2)
        );
    }

    public function test_admin_can_update_river_with_states_and_fish(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $river = River::factory()->create(['name' => 'Alte Donau']);
        $oldState = State::factory()->create();
        $oldFish = Fish::factory()->create();
        $river->states()->sync([$oldState->id]);
        $river->fish()->sync([$oldFish->id]);

        $newStates = State::factory()->count(2)->create();
        $newFish = Fish::factory()->count(2)->create();

        $data = [
            'name' => 'Neue Donau',
            'desc' => 'Updated Description',
            'hint' => 'Abends angeln erlaubt',
            'fishing_rights' => 'Erlaubt',
            'ticket_sales' => 'Online',
            'states' => $newStates->pluck('id')->toArray(),
            'fish' => $newFish->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($admin)->put(route('admin.river.update', $river), $data);
        $response->assertRedirect(route('admin.river.index'));
        $response->assertSessionHas('success', 'Fluss aktualisiert!');

        $river->refresh();
        $this->assertEquals('Neue Donau', $river->name);
        $this->assertCount(2, $river->states);
        $this->assertCount(2, $river->fish);
    }

    public function test_admin_can_delete_river(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $river = River::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.river.destroy', $river));
        $response->assertRedirect(route('admin.river.index'));
        $response->assertSessionHas('success', 'Fluss gelöscht!');

        $this->assertDatabaseMissing('rivers', ['id' => $river->id]);
    }
}
