<?php

namespace Tests\Feature;

use App\Models\Catched;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_reset_removes_visitor_data_and_recreates_three_catches(): void
    {
        Queue::fake();
        Storage::fake('public');
        $this->seed(\Database\Seeders\StatesSeeder::class);
        $this->seed(\Database\Seeders\FishSeeder::class);
        $this->seed(\Database\Seeders\LakeSeeder::class);
        $this->seed(\Database\Seeders\RiverSeeder::class);

        $this->artisan('demo:reset')->assertSuccessful();

        $demo = User::where('email', User::DEMO_EMAIL)->firstOrFail();
        $this->assertSame(3, $demo->catched()->count());
        $original = $demo->catched()->pluck('id');

        // Besucher legen Daten an
        Catched::factory()->forUser($demo)->count(2)->create();
        $demo->spots()->create(['name' => 'Geheimspot', 'latitude' => 46.6, 'longitude' => 13.9]);
        $demo->forceFill(['name' => 'Verändert'])->save();

        $this->artisan('demo:reset')->assertSuccessful();

        $demo->refresh();
        $this->assertSame('Demo Angler', $demo->name);
        $this->assertSame(3, $demo->catched()->count());
        $this->assertSame(0, $demo->spots()->count());
        $this->assertEmpty($original->intersect($demo->catched()->pluck('id')), 'Fänge werden neu angelegt');
        $this->assertSame(3, \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', Catched::class)->count(), 'alte Fotos entfernt');
    }

    public function test_demo_reset_is_scheduled_at_midnight_vienna_time(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn($event) => str_contains($event->command, 'demo:reset'));

        $this->assertNotNull($event);
        $this->assertSame('0 0 * * *', $event->expression);
        $this->assertSame('Europe/Vienna', $event->timezone);
    }
}
