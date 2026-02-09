<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use App\Models\Fish;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FishControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_fish_index(): void
    {
        $response = $this->get(route('app.fish.index'));
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_fish_index_with_media(): void
    {
        $user = User::factory()->create();
        $fish = Fish::factory()->create(['name' => 'Karpfen']);

        // Fake Storage
        Storage::fake('public');

        // Datei zur MediaLibrary hinzufügen
        $file = UploadedFile::fake()->image('fish1.jpg');
        $fish->addMedia($file)->toMediaCollection('fish', 'public');

        $response = $this->actingAs($user)->get(route('app.fish.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Fish/Index')
                ->has(
                    'fish',
                    1,
                    fn($page) => $page
                        ->where('id', $fish->id)
                        ->where('name', 'Karpfen')
                        ->where('media', [
                            ['url' => $fish->getFirstMediaUrl('fish')]
                        ])
                        ->etc()
                )
        );

        // Session wurde geleert
        $this->assertFalse(session()->has('meta'));
    }

    public function test_authenticated_user_can_view_single_fish_with_media(): void
    {
        $user = User::factory()->create();
        $fish = Fish::factory()->create(['name' => 'Hecht', 'desc' => 'Ein Raubfisch']);

        // Fake Storage
        Storage::fake('public');

        // Datei zur MediaLibrary hinzufügen
        $file = UploadedFile::fake()->image('fish2.jpg');
        $fish->addMedia($file)->toMediaCollection('fish', 'public');

        $response = $this->actingAs($user)->get(route('app.fish.show', $fish));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Fish/Show')
                ->where('fish.id', $fish->id)
                ->where('fish.name', 'Hecht')
                ->where('fish.desc', 'Ein Raubfisch')
                ->where('fish.media', [
                    ['url' => $fish->getFirstMediaUrl('fish')]
                ])
                ->etc()
        );

        // Session wurde geleert
        $this->assertFalse(session()->has('meta'));
    }
}
