<?php

namespace Tests\Feature\Controller\Admin;

use Tests\TestCase;
use App\Models\Fish;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FishAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_fish_admin_routes(): void
    {
        $fish = Fish::factory()->create();

        $this->get(route('admin.fish.index'))->assertRedirect('/login');
        $this->get(route('admin.fish.create'))->assertRedirect('/login');
        $this->get(route('admin.fish.edit', $fish))->assertRedirect('/login');
        $this->post(route('admin.fish.update', $fish), [])->assertRedirect('/login');
        $this->delete(route('admin.fish.destroy', $fish))->assertRedirect('/login');
    }

    public function test_user_cannot_access_fish_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $fish = Fish::factory()->create();

        $this->actingAs($user)->get(route('admin.fish.index'))->assertRedirect('/dashboard');
        $this->actingAs($user)->get(route('admin.fish.create'))->assertRedirect('/dashboard');
        $this->actingAs($user)->get(route('admin.fish.edit', $fish))->assertRedirect('/dashboard');
        $this->actingAs($user)->post(route('admin.fish.update', $fish), [])->assertRedirect('/dashboard');
        $this->actingAs($user)->delete(route('admin.fish.destroy', $fish))->assertRedirect('/dashboard');
    }

    /**
     * 🔹 Admin kann Fisch-Index sehen
     */
    public function test_admin_can_view_fish_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $fish = Fish::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('admin.fish.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Admin/Fish/Index')
                ->has('fishs', 2)
        );
    }

    /**
     * 🔹 Normale User können Index nicht sehen
     */
    public function test_user_cannot_access_fish_index(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.fish.index'));

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error', 'Du hast keinen Zugriff.');
    }

    /**
     * 🔹 Admin kann Create-Seite sehen
     */
    public function test_admin_can_view_fish_create(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.fish.create'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Admin/Fish/Create')
        );
    }

    /**
     * 🔹 Admin kann einen Fisch erstellen inkl. Foto
     */
    public function test_admin_can_store_fish_with_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $file = UploadedFile::fake()->image('fish.jpg');

        $data = [
            'name' => 'Forelle',
            'desc' => 'Ein schöner Fisch',
            'photo' => $file,
        ];

        $response = $this->actingAs($admin)->post(route('admin.fish.store'), $data);

        $response->assertRedirect(route('admin.fish.index'));
        $response->assertSessionHas('success', 'Fisch erstellt!');

        $fish = Fish::first();
        $this->assertNotNull($fish);
        $this->assertEquals('Forelle', $fish->name);
        $this->assertCount(1, $fish->getMedia('fish'));
    }

    /**
     * 🔹 Admin kann Edit-Seite sehen inkl. Media
     */
    public function test_admin_can_view_fish_edit_with_media(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $fish = Fish::factory()->create();

        $fish->addMedia(UploadedFile::fake()->image('fish.jpg'))
            ->toMediaCollection('fish');

        $response = $this->actingAs($admin)->get(route('admin.fish.edit', $fish));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Admin/Fish/Edit')
                ->where('fish.id', $fish->id)
                ->has('fish.media', 1)
        );
    }

    /**
     * 🔹 Admin kann Fisch aktualisieren inkl. Foto ersetzen
     */
    public function test_admin_can_update_fish_with_new_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $fish = Fish::factory()->create();
        $fish->addMedia(UploadedFile::fake()->image('old.jpg'))->toMediaCollection('fish');

        $newFile = UploadedFile::fake()->image('new.jpg');
        $data = [
            'name' => 'Forelle Updated',
            'desc' => 'Neue Beschreibung',
            'photo' => $newFile,
        ];

        // Prüfen, dass Route PATCH oder PUT genutzt wird:
        $response = $this->actingAs($admin)
            ->post(route('admin.fish.update', $fish), $data);

        $response->assertRedirect(route('admin.fish.index'));
        $response->assertSessionHas('success', 'Fisch aktualisiert!');

        $fish->refresh();
        $this->assertEquals('Forelle Updated', $fish->name);
        $this->assertCount(1, $fish->getMedia('fish')); // alte Datei gelöscht, neue da
    }

    /**
     * 🔹 Admin kann Fisch löschen
     */
    public function test_admin_can_destroy_fish(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $fish = Fish::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.fish.destroy', $fish));

        $response->assertRedirect(route('admin.fish.index'));
        $response->assertSessionHas('success', 'Fisch gelöscht!');

        $this->assertDatabaseMissing('fish', ['id' => $fish->id]);
    }

    /**
     * 🔹 Admin kann Foto löschen
     */
    public function test_admin_can_delete_fish_photo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $fish = Fish::factory()->create();

        // Media hinzufügen
        $file = UploadedFile::fake()->image('fish.jpg');
        $fish->addMedia($file)->toMediaCollection('fish');

        $media = $fish->getFirstMedia('fish');
        $this->assertCount(1, $fish->getMedia('fish'));

        // Route aufrufen
        $response = $this->actingAs($admin)
            ->delete(route('admin.fish.photo.delete', $media->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Bild gelöscht.');

        // Fresh Fish & Media neu laden
        $freshFish = $fish->fresh();
        $this->assertCount(0, $freshFish->getMedia('fish'));
    }
}
