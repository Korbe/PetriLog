<?php

namespace Tests\Feature\Controller\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\State;
use App\Models\Association;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssociationAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_association_admin_routes(): void
    {
        $association = Association::factory()->create();

        $this->get(route('admin.association.index'))->assertRedirect('/login');
        $this->get(route('admin.association.create'))->assertRedirect('/login');
        $this->get(route('admin.association.edit', $association))->assertRedirect('/login');
        $this->patch(route('admin.association.update', $association), [])->assertRedirect('/login');
        $this->delete(route('admin.association.destroy', $association))->assertRedirect('/login');
    }

    public function test_user_cannot_access_association_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $association = Association::factory()->create();

        $this->actingAs($user)->get(route('admin.association.index'))->assertRedirect('/dashboard');
        $this->actingAs($user)->get(route('admin.association.create'))->assertRedirect('/dashboard');
        $this->actingAs($user)->get(route('admin.association.edit', $association))->assertRedirect('/dashboard');
        $this->actingAs($user)->patch(route('admin.association.update', $association), [])->assertRedirect('/dashboard');
        $this->actingAs($user)->delete(route('admin.association.destroy', $association))->assertRedirect('/dashboard');
    }

    /**
     * 🔹 Admin kann alle Vereine auflisten
     */
    public function test_admin_can_view_association_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $state = State::factory()->create();
        $associations = Association::factory()->count(2)->create(['state_id' => $state->id]);

        $response = $this->actingAs($admin)->get(route('admin.association.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Admin/Association/Index')
                ->has('associations', 2)
        );
    }

    /**
     * 🔹 Admin kann die Create-Seite sehen
     */
    public function test_admin_can_view_association_create(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $states = State::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('admin.association.create'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Admin/Association/Create')
                ->has('states', 2)
        );
    }

    public function test_normal_user_cannot_access_association_create(): void
    {
        // Erstelle einen normalen User (kein Admin)
        $user = User::factory()->create(['is_admin' => false]);

        // Versuche, die create-Seite zu besuchen
        $response = $this->actingAs($user)->get(route('admin.association.create'));

        // Erwartet einen Redirect (302) auf Dashboard
        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');

        // Optional: Prüfen, dass eine Fehlermeldung in der Session ist
        $response->assertSessionHas('error', 'Du hast keinen Zugriff.');
    }


    /**
     * 🔹 Admin kann einen Verein erstellen
     */
    public function test_admin_can_store_association(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $state = State::factory()->create();

        $data = [
            'name' => 'Angelverein Mustermann',
            'desc' => 'Beschreibung',
            'link' => 'https://example.test',
            'state_id' => $state->id,
        ];

        $response = $this->actingAs($admin)->post(route('admin.association.store'), $data);

        $response->assertRedirect(route('admin.association.index'));
        $response->assertSessionHas('success', 'Verein erstellt!');
        $this->assertDatabaseHas('associations', ['name' => 'Angelverein Mustermann']);
    }

    /**
     * 🔹 Admin kann die Edit-Seite sehen
     */
    public function test_admin_can_view_association_edit(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $state = State::factory()->create();
        $association = Association::factory()->create(['state_id' => $state->id]);
        $states = State::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('admin.association.edit', $association));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Admin/Association/Edit')
                ->where('association.id', $association->id)
                ->has('states', 2 + 1) // +1 für das bereits vorhandene State
        );
    }

    /**
     * 🔹 Admin kann einen Verein aktualisieren
     */
    public function test_admin_can_update_association(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $state = State::factory()->create();
        $association = Association::factory()->create(['state_id' => $state->id]);

        $data = [
            'name' => 'Updated Verein',
            'desc' => 'Neue Beschreibung',
            'link' => 'https://updated.test',
            'state_id' => $state->id,
        ];

        $response = $this->actingAs($admin)->put(route('admin.association.update', $association), $data);

        $response->assertRedirect(route('admin.association.index'));
        $response->assertSessionHas('success', 'Verein aktualisiert!');
        $this->assertDatabaseHas('associations', ['name' => 'Updated Verein']);
    }

    /**
     * 🔹 Admin kann einen Verein löschen
     */
    public function test_admin_can_destroy_association(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $association = Association::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.association.destroy', $association));

        $response->assertRedirect(route('admin.association.index'));
        $response->assertSessionHas('success', 'Verein gelöscht!');
        $this->assertDatabaseMissing('associations', ['id' => $association->id]);
    }
}
