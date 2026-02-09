<?php

namespace Tests\Feature\Controller\Admin;

use Tests\TestCase;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\User;
use App\Models\State;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LakeAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_lake_admin_routes(): void
    {
        $lake = Lake::factory()->create();

        $this->get(route('admin.lake.index'))->assertRedirect('/login');
        $this->get(route('admin.lake.create'))->assertRedirect('/login');
        $this->get(route('admin.lake.edit', $lake))->assertRedirect('/login');
        $this->put(route('admin.lake.update', $lake), [])->assertRedirect('/login');
        $this->delete(route('admin.lake.destroy', $lake))->assertRedirect('/login');
    }

    public function test_user_cannot_access_lake_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $lake = Lake::factory()->create();

        $this->actingAs($user)->get(route('admin.lake.index'))->assertRedirect('/dashboard');
        $this->actingAs($user)->get(route('admin.lake.create'))->assertRedirect('/dashboard');
        $this->actingAs($user)->get(route('admin.lake.edit', $lake))->assertRedirect('/dashboard');
        $this->actingAs($user)->put(route('admin.lake.update', $lake), [])->assertRedirect('/dashboard');
        $this->actingAs($user)->delete(route('admin.lake.destroy', $lake))->assertRedirect('/dashboard');
    }

    public function test_admin_can_view_lake_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $lakes = Lake::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('admin.lake.index'));
        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/Lake/Index')
                ->has('lakes', 2)
        );
    }

    public function test_admin_can_view_lake_create_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        State::factory()->count(2)->create();
        Fish::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('admin.lake.create'));
        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/Lake/Create')
                ->has('states', 2)
                ->has('fish', 2)
        );
    }

    public function test_admin_can_store_lake_with_states_and_fish(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $states = State::factory()->count(2)->create();
        $fish = Fish::factory()->count(2)->create();

        $data = [
            'name' => 'Testsee',
            'desc' => 'Ein schöner See',
            'hint' => 'Nur tagsüber angeln',
            'fishing_rights' => 'Angelschein erforderlich',
            'ticket_sales' => 'Am Kiosk',
            'states' => $states->pluck('id')->toArray(),
            'fish' => $fish->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($admin)->post(route('admin.lake.store'), $data);

        $response->assertRedirect(route('admin.lake.index'));
        $response->assertSessionHas('success', 'See erstellt!');

        $lake = Lake::first();
        $this->assertEquals('Testsee', $lake->name);
        $this->assertCount(2, $lake->states);
        $this->assertCount(2, $lake->fish);
    }

    public function test_admin_can_view_lake_edit_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $lake = Lake::factory()->create();
        $states = State::factory()->count(2)->create();
        $fish = Fish::factory()->count(2)->create();

        $lake->states()->sync($states->pluck('id'));
        $lake->fish()->sync($fish->pluck('id'));

        $response = $this->actingAs($admin)->get(route('admin.lake.edit', $lake));
        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/Lake/Edit')
                ->has('lake.states', 2)
                ->has('lake.fish', 2)
                ->has('states', 2)
                ->has('fish', 2)
        );
    }

    public function test_admin_can_update_lake_with_states_and_fish(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $lake = Lake::factory()->create(['name' => 'Altsee']);
        $oldState = State::factory()->create();
        $oldFish = Fish::factory()->create();
        $lake->states()->sync([$oldState->id]);
        $lake->fish()->sync([$oldFish->id]);

        $newStates = State::factory()->count(2)->create();
        $newFish = Fish::factory()->count(2)->create();

        $data = [
            'name' => 'Neuer See',
            'desc' => 'Updated Description',
            'hint' => 'Abends angeln erlaubt',
            'fishing_rights' => 'Erlaubt',
            'ticket_sales' => 'Online',
            'states' => $newStates->pluck('id')->toArray(),
            'fish' => $newFish->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($admin)->put(route('admin.lake.update', $lake), $data);
        $response->assertRedirect(route('admin.lake.index'));
        $response->assertSessionHas('success', 'See aktualisiert!');

        $lake->refresh();
        $this->assertEquals('Neuer See', $lake->name);
        $this->assertCount(2, $lake->states);
        $this->assertCount(2, $lake->fish);
    }

    public function test_admin_can_delete_lake(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $lake = Lake::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.lake.destroy', $lake));
        $response->assertRedirect(route('admin.lake.index'));
        $response->assertSessionHas('success', 'See gelöscht!');

        $this->assertDatabaseMissing('lakes', ['id' => $lake->id]);
    }
}
