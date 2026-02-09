<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\User;
use App\Models\River;
use App\Models\Catched;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_longest_catch_returns_the_catched_with_max_length(): void
    {
        $short = Catched::factory()->create([
            'user_id' => $this->user->id,
            'length' => 10,
        ]);
        $long = Catched::factory()->create([
            'user_id' => $this->user->id,
            'length' => 50,
        ]);

        $controller = new DashboardController();
        $result = $controller->longestCatch($this->user->id);

        $this->assertEquals($long->id, $result->id);
    }

    public function test_heaviest_catch_returns_the_catched_with_max_weight(): void
    {
        $light = Catched::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 1000,
        ]);
        $heavy = Catched::factory()->create([
            'user_id' => $this->user->id,
            'weight' => 5000,
        ]);

        $controller = new DashboardController();
        $result = $controller->heaviestCatch($this->user->id);

        $this->assertEquals($heavy->id, $result->id);
    }

    public function test_favorite_fish_returns_name_of_most_catched_fish(): void
    {
        $fish1 = Fish::factory()->create(['name' => 'Forelle']);
        $fish2 = Fish::factory()->create(['name' => 'Karpfen']);

        Catched::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'fish_id' => $fish1->id,
        ]);
        Catched::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'fish_id' => $fish2->id,
        ]);

        $controller = new DashboardController();
        $result = $controller->favoriteFish($this->user->id);

        $this->assertEquals('Forelle', $result);
    }

    public function test_favorite_locations_returns_most_frequent_lake_or_river(): void
    {
        $lake1 = Lake::factory()->create(['name' => 'Bodensee']);
        $river1 = River::factory()->create(['name' => 'Donau']);

        Catched::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'lake_id' => $lake1->id,
        ]);
        Catched::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'river_id' => $river1->id,
        ]);

        $controller = new DashboardController();
        $result = $controller->favoriteLocations($this->user->id);

        $this->assertEquals('Bodensee', $result);
    }

    public function test_record_catches_per_day_returns_max_catches(): void
    {
        $today = now()->startOfDay();
        Catched::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'date' => $today,
        ]);
        Catched::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'date' => $today->subDay(),
        ]);

        $controller = new DashboardController();
        $result = $controller->recordCatchesPerDay($this->user->id);

        $this->assertEquals(5, $result);
    }

    public function test_recent_catches_returns_latest_two_entries(): void
    {
        $c1 = Catched::factory()->create(['user_id' => $this->user->id, 'created_at' => now()->subDays(3)]);
        $c2 = Catched::factory()->create(['user_id' => $this->user->id, 'created_at' => now()->subDays(2)]);
        $c3 = Catched::factory()->create(['user_id' => $this->user->id, 'created_at' => now()->subDay()]);

        $controller = new DashboardController();
        $result = $controller->recentCatches($this->user->id);

        $this->assertCount(2, $result);
        $this->assertEquals($c3->id, $result[0]['id']);
        $this->assertEquals($c2->id, $result[1]['id']);
    }
}
