<?php

namespace Tests\Feature\Controller\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\State;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

class StateAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_state_admin_routes(): void
    {
        $state = State::factory()->create();

        $this->get(route('admin.state.index'))->assertRedirect('/login');
        $this->get(route('admin.state.create'))->assertRedirect('/login');
        $this->get(route('admin.state.edit', $state))->assertRedirect('/login');
        $this->post(route('admin.state.update', $state), [])->assertRedirect('/login');
        $this->delete(route('admin.state.destroy', $state))->assertRedirect('/login');
    }

        public function test_user_cannot_access_state_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $state = State::factory()->create();

        $this->actingAs($user)->get(route('admin.state.index'))->assertRedirect('/dashboard');
        $this->actingAs($user)->get(route('admin.state.create'))->assertRedirect('/dashboard');
        $this->actingAs($user)->get(route('admin.state.edit', $state))->assertRedirect('/dashboard');
        $this->actingAs($user)->post(route('admin.state.update', $state), [])->assertRedirect('/dashboard');
        $this->actingAs($user)->delete(route('admin.state.destroy', $state))->assertRedirect('/dashboard');
    }

    public function test_admin_can_view_state_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $states = State::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.state.index'));
        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/State/Index')
                ->has('states', 3)
        );
    }

    public function test_admin_can_view_state_create_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.state.create'));
        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/State/Create')
        );
    }

    public function test_admin_can_store_state_with_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $file = UploadedFile::fake()->image('state.jpg');
        $data = [
            'name' => 'Bayern',
            'desc' => 'Schönes Bundesland',
            'photo' => $file,
        ];

        $response = $this->actingAs($admin)->post(route('admin.state.store'), $data);

        $response->assertRedirect(route('admin.state.index'));
        $response->assertSessionHas('success', 'Bundesland erstellt!');

        $state = State::first();
        $this->assertEquals('Bayern', $state->name);
        $this->assertCount(1, $state->getMedia('state'));
    }

    public function test_admin_can_view_state_edit_form_with_media(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $state = State::factory()->create();
        $state->addMedia(UploadedFile::fake()->image('state.jpg'))->toMediaCollection('state');

        $response = $this->actingAs($admin)->get(route('admin.state.edit', $state));
        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Admin/State/Edit')
                ->has('state.media', 1)
        );
    }

    public function test_admin_can_update_state_with_new_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $state = State::factory()->create(['name' => 'OldName', 'desc' => 'OldDesc']);
        $state->addMedia(UploadedFile::fake()->image('old.jpg'))->toMediaCollection('state');

        $newFile = UploadedFile::fake()->image('new.jpg');
        $data = [
            'name' => 'NewName',
            'desc' => 'NewDesc',
            'photo' => $newFile,
        ];

        $response = $this->actingAs($admin)->post(route('admin.state.update', $state), $data);

        $response->assertRedirect(route('admin.state.index'));
        $response->assertSessionHas('success', 'Bundesland aktualisiert!');

        $state->refresh();
        $this->assertEquals('NewName', $state->name);
        $this->assertEquals('NewDesc', $state->desc);
        $this->assertCount(1, $state->getMedia('state')); // alte Datei ersetzt
    }

    public function test_admin_can_delete_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $state = State::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.state.destroy', $state));

        $response->assertRedirect(route('admin.state.index'));
        $this->assertDatabaseMissing('states', ['id' => $state->id]);
    }

    public function test_admin_can_delete_state_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $state = State::factory()->create();
        $media = $state->addMedia(UploadedFile::fake()->image('state.jpg'))->toMediaCollection('state');

        $this->assertCount(1, $state->getMedia('state'));

        $response = $this->actingAs($admin)->delete(route('admin.state.photo.delete', $media->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Bild gelöscht.');
        $this->assertCount(0, $state->fresh()->getMedia('state'));
    }
}
