<?php

namespace Tests\Feature\Controller;

use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Lake $lake;
    private Fish $fish;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->lake = Lake::factory()->create(['name' => 'Faaker See']);
        $this->fish = Fish::factory()->create(['name' => 'Hecht']);
    }

    private function catchFor(User $user, array $attributes = []): Catched
    {
        return Catched::factory()->forUser($user)->create([
            'fish_id' => $this->fish->id,
            'lake_id' => $this->lake->id,
            'river_id' => null,
            'date' => '2026-06-01 08:00:00',
            'length' => 70,
            'weight' => 2500,
            'released' => false,
            'bait' => 'Gummifisch',
            ...$attributes,
        ]);
    }

    public function test_index_summarises_kept_and_released_catches_of_the_year(): void
    {
        $this->catchFor($this->user);
        $this->catchFor($this->user, ['released' => true, 'weight' => 900]);
        $this->catchFor($this->user, ['released' => null]);
        $this->catchFor($this->user, ['date' => '2025-06-01 08:00:00']);
        $this->catchFor(User::factory()->create());

        $this->actingAs($this->user)
            ->get(route('app.report.index', ['year' => 2026]))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Report/Index')
                ->where('years', [2026, 2025])
                ->where('totals.count', 3)
                ->where('totals.kept', 1)
                ->where('totals.released', 1)
                ->where('totals.unknown', 1)
                ->where('totals.keptWeight', 2500)
                ->where('summary.0.water', 'Faaker See')
                ->where('summary.0.fish.0.name', 'Hecht')
                ->where('summary.0.fish.0.maxLength', 70)
                ->has('catches', 3));
    }

    public function test_csv_export_contains_only_own_catches_and_neutralises_formulas(): void
    {
        $this->catchFor($this->user, ['bait' => '=HYPERLINK("x")']);
        $this->catchFor(User::factory()->create(), ['bait' => 'Fremder Köder']);

        $response = $this->actingAs($this->user)->get(route('app.report.export', ['year' => 2026]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Faaker See', $csv);
        $this->assertStringContainsString('Entnommen', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('Fremder Köder', $csv);
    }

    public function test_print_view_renders(): void
    {
        $this->catchFor($this->user);

        $this->actingAs($this->user)
            ->get(route('app.report.print', ['year' => 2026, 'water' => "lake-{$this->lake->id}"]))
            ->assertOk()
            ->assertSee('Fangstatistik 2026')
            ->assertSee('Faaker See');
    }

    public function test_release_status_can_be_set_only_for_own_catches(): void
    {
        $own = $this->catchFor($this->user, ['released' => null]);
        $foreign = $this->catchFor(User::factory()->create(), ['released' => null]);

        $this->actingAs($this->user)->patch(route('app.report.release', $own), ['released' => true])->assertRedirect();
        $this->assertTrue($own->fresh()->released);

        $this->actingAs($this->user)->patch(route('app.report.release', $foreign), ['released' => true])->assertForbidden();
        $this->assertNull($foreign->fresh()->released);
    }
}
