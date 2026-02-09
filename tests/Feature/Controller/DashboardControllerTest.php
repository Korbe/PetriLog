<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\River;
use App\Models\Catched;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_expected_inertia_props_with_correct_values(): void
    {
        Storage::fake('public');

        // Benutzer erstellen
        $user = User::factory()->create();

        // Fische und Gewässer
        $fish = Fish::factory()->create(['name' => 'Forelle']);
        $lake = Lake::factory()->create(['name' => 'Bodensee']);
        $river = River::factory()->create(['name' => 'Donau']);

        // Catched-Einträge erstellen
        $catchedWithLake = Catched::factory()->create([
            'user_id' => $user->id,
            'fish_id' => $fish->id,
            'lake_id' => $lake->id,
            'river_id' => null,
            'length' => 50,
            'weight' => 3000,
            'date' => now()->subDays(2),
            'created_at' => now()->subDays(2),
        ]);

        $catchedWithRiver = Catched::factory()->create([
            'user_id' => $user->id,
            'fish_id' => $fish->id,
            'lake_id' => null,
            'river_id' => $river->id,
            'length' => 60,
            'weight' => 3500,
            'date' => now()->subDay(),
            'created_at' => now()->subDay(),
        ]);

        // Media hinzufügen
        $catchedWithLake->addMedia(UploadedFile::fake()->image('lake.jpg'))->toMediaCollection('photos');
        $catchedWithRiver->addMedia(UploadedFile::fake()->image('river.jpg'))->toMediaCollection('photos');

        // Dashboard aufrufen
        $response = $this->actingAs($user)->get(route('app.dashboard'));

        $response->assertStatus(200);

        $response->assertInertia(function (AssertableInertia $page) use ($user, $catchedWithLake, $catchedWithRiver, $lake, $river, $fish) {
            $page->component('Dashboard/Index');

            // Gesamtanzahl der Fänge prüfen
            $page->where('catchedCount', 2);

            // Schwerster Fang
            $page->where('heaviestCatch.id', $catchedWithRiver->id)
                ->where('heaviestCatch.length', 60)
                ->where('heaviestCatch.weight', 3500);

            // Längster Fang
            $page->where('longestCatch.id', $catchedWithRiver->id)
                ->where('longestCatch.length', 60);

            // Routen prüfen
            $page->where('createUrl', route('app.catched.create'))
                ->where('routeHeaviest', route('app.catched.show', $catchedWithRiver->id))
                ->where('routeLongest', route('app.catched.show', $catchedWithRiver->id));

            // Lieblingsfisch
            $page->where('favoriteFish', $fish->name);

            // Lieblingsgewässer (Fluss) - angepasst, damit der Fluss gewinnt bei Gleichstand
            $page->where('favoriteLocation', $river->name);

            // Neueste Fänge
            $page->has('recentCatches', 2);
            $page->where('recentCatches.0.id', $catchedWithRiver->id)
                ->where('recentCatches.0.fish.id', $fish->id)
                ->where('recentCatches.0.lake', null)
                ->where('recentCatches.0.river.id', $river->id);

            // Ältester Fang: See
            $page->where('recentCatches.1.id', $catchedWithLake->id)
                ->where('recentCatches.1.fish.id', $fish->id)
                ->where('recentCatches.1.lake.id', $lake->id)
                ->where('recentCatches.1.river', null);

            // Statistiken prüfen
            $page->has('catchedStatsMonthly.timestamps')
                ->has('catchedStatsMonthly.scores')
                ->has('catchedStatsYearly.timestamps')
                ->has('catchedStatsYearly.scores');

            // Tag mit den meisten Fängen
            $page->where('mostCatchesDay', 1);
        });
    }
}
