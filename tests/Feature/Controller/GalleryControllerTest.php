<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lake;
use App\Models\River;
use App\Models\Catched;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

class GalleryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_gallery_with_only_catches_that_have_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $lake = Lake::factory()->create(['name' => 'Starnberger See']);
        $river = River::factory()->create(['name' => 'Isar']);

        // 🔹 Mit Foto → sichtbar
        $withPhoto = Catched::factory()->create([
            'user_id' => $user->id,
            'lake_id' => $lake->id,
            'date' => now()->subDays(2),
        ]);
        $withPhoto
            ->addMedia(UploadedFile::fake()->image('fish.jpg'))
            ->toMediaCollection('photos');

        // 🔹 Ohne Foto → darf NICHT erscheinen
        Catched::factory()->create([
            'user_id' => $user->id,
            'river_id' => $river->id,
            'date' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('app.gallery.index'));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Gallery/Index')
                ->has('catcheds', 1)
                ->has(
                    'catcheds.0',
                    fn(Assert $c) =>
                    $c->where('id', $withPhoto->id)
                        ->where('name', $withPhoto->name)
                        ->where('date', $withPhoto->date->format('d.m.Y'))
                        ->where('water.type', 'lake')
                        ->where('water.name', 'Starnberger See')
                        ->has('images', 1)
                        ->has('images.0.url')
                )
                ->has('dateRange.startDate')
                ->has('dateRange.endDate')
        );
    }

    public function test_gallery_can_be_filtered_by_date_and_description(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $catchedWithDescription = Catched::factory()->create([
            'user_id' => $user->id,
            'remark' => 'Schöner Fang',
            'date' => now()->subDays(5),
        ]);
        $catchedWithDescription
            ->addMedia(UploadedFile::fake()->image('ok.jpg'))
            ->toMediaCollection('photos');

        // ❌ Ohne Beschreibung
        $catchedWithoutDescription = Catched::factory()->create([
            'user_id' => $user->id,
            'remark' => null,
            'date' => now()->subDays(5),
        ]);
        $catchedWithoutDescription
            ->addMedia(UploadedFile::fake()->image('no.jpg'))
            ->toMediaCollection('photos');

        $response = $this->actingAs($user)->get(route('app.gallery.index', [
            'onlyWithDescription' => true,
            'startDate' => now()->subDays(6)->toDateString(),
            'endDate' => now()->subDays(4)->toDateString(),
        ]));

        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Gallery/Index')
                ->has('catcheds', 1)
                ->where('catcheds.0.id', $catchedWithDescription->id)
        );
    }
}
