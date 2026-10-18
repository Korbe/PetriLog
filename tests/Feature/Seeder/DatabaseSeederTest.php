<?php

namespace Tests\Feature\Seeder;

use App\Models\Association;
use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\River;
use App\Models\State;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AssociationSeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\FishSeeder;
use Database\Seeders\LakeSeeder;
use Database\Seeders\RiverSeeder;
use Database\Seeders\StatesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * DatabaseSeeder selbst läuft nur in 'local' → Seeder in derselben Reihenfolge direkt aufrufen.
     */
    private const SEEDERS = [
        StatesSeeder::class,
        AssociationSeeder::class,
        FishSeeder::class,
        LakeSeeder::class,
        RiverSeeder::class,
        AdminUserSeeder::class,
        DemoUserSeeder::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Nichts in storage/app/public schreiben, Bild-Konvertierungen nicht ausführen
        Storage::fake('public');
        Queue::fake();
    }

    public function test_seeders_create_master_data_and_demo_user(): void
    {
        $this->seed(self::SEEDERS);

        $this->assertSeededState();
    }

    public function test_seeding_twice_creates_no_duplicates(): void
    {
        $this->seed(self::SEEDERS);
        $this->seed(self::SEEDERS);

        $this->assertSeededState();
        $this->assertSame(2, User::count());
    }

    private function assertSeededState(): void
    {
        $this->assertSame(9, State::count());
        $this->assertSame(3, Association::count());
        $this->assertSame(34, Fish::count());
        $this->assertSame(66, Lake::count());
        $this->assertSame(25, River::count());

        // Genau ein Default je Tabelle
        foreach ([Fish::class, Lake::class, River::class] as $model) {
            $this->assertSame(['Anderes / nicht gelistet'], $model::where('is_default', true)->pluck('name')->all(), $model);
        }

        $this->assertSame(407, DB::table('fish_lake')->count());
        $this->assertSame(48, DB::table('fish_river')->count());
        $this->assertSame(66, DB::table('lake_state')->count()); // Wolfgangsee liegt in OÖ und Salzburg, Default hat keins

        // Bilder: jedes Bundesland ein Wappen, jeder Fisch außer dem Default ein Bild
        $this->assertSame(9, Media::where('collection_name', 'state')->count());
        $this->assertSame(33, Media::where('collection_name', 'fish')->count());
        State::all()->each(fn (State $state) => $this->assertCount(1, $state->getMedia('state'), $state->name));
        // Wappen wie auf dem Produktivsystem mit großem Anfangsbuchstaben (Linux unterscheidet Groß-/Kleinschreibung)
        $this->assertSame('Niederoesterreich.png', State::where('name', 'Niederösterreich')->first()->getFirstMedia('state')->file_name);
        Storage::disk('public')->assertExists(State::first()->getFirstMedia('state')->getPathRelativeToRoot());

        // Seed-Assets dürfen nicht verschoben werden
        $this->assertFileExists(database_path('seeders/assets/states/Kaernten.png'));
        $this->assertFileExists(database_path('seeders/assets/catches/hecht-1.jpg'));

        $demo = User::where('email', DemoUserSeeder::EMAIL)->firstOrFail();
        $this->assertTrue($demo->hasVerifiedEmail());
        $this->assertSame('Kärnten', $demo->state->name);

        $catches = $demo->catched()->with('media')->get();
        $this->assertCount(3, $catches);
        $this->assertSame(3, Catched::count());
        $this->assertSame(3, Media::where('collection_name', 'photos')->count());
        $this->assertTrue($catches->every(fn (Catched $c) => $c->lake_id xor $c->river_id));
        $this->assertTrue($catches->every(fn (Catched $c) => $c->date->isPast() && $c->date->gt(now()->subYear())));

        // Dashboard-Highlights (längster/schwerster Fang) sind Fänge mit Foto
        $longest = $catches->sortByDesc('length')->first();
        $heaviest = $catches->sortByDesc('weight')->first();
        $this->assertSame('Spiegelkarpfen', $longest->fish->name);
        $this->assertSame('Spiegelkarpfen', $heaviest->fish->name);
        $this->assertNotEmpty($longest->getMedia('photos'));
        $this->assertNotEmpty($heaviest->getMedia('photos'));
    }

    public function test_demo_user_can_still_create_one_catch_without_subscription(): void
    {
        $this->seed(self::SEEDERS);
        $this->withoutVite();

        $demo = User::where('email', DemoUserSeeder::EMAIL)->firstOrFail();

        // Limit für User ohne Abo: ab 5 Fängen leitet create() mit Fehlermeldung um
        $this->actingAs($demo)
            ->get(route('app.catched.create'))
            ->assertOk();
    }
}
