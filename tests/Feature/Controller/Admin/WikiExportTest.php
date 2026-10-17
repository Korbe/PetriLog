<?php

namespace Tests\Feature\Controller\Admin;

use App\Models\Fish;
use App\Models\Lake;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use ZipArchive;

class WikiExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);

        $state = State::factory()->create(['name' => 'Kärnten']);
        $hecht = Fish::factory()->create(['name' => 'Hecht', 'desc' => '<p>Raubfisch</p>']);
        $pending = Fish::factory()->create(['name' => 'Geheimfisch']);
        $pending->forceFill(['is_approved' => false, 'submitted_by' => $this->admin->id])->save();

        $lake = Lake::factory()->create(['name' => 'Wörthersee', 'ticket_sales' => '<p>Trafik</p>']);
        $lake->states()->sync([$state->id]);
        $lake->fish()->sync([$hecht->id, $pending->id]);
    }

    public function test_page_lists_all_datasets(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.wiki-export.index'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Admin/WikiExport/Index')
                ->has('datasets', 5)
                ->where('datasets.2.name', 'fish')
                ->where('datasets.2.count', 1));
    }

    public function test_single_dataset_matches_the_seeder_format_without_pending_entries(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.wiki-export.dataset', 'lakes'));

        $response->assertOk()->assertDownload('lakes.json');
        $json = $response->streamedContent();

        // Wie die bestehenden Seed-Dateien: Umlaute und Slashes unescaped, 4 Leerzeichen Einrückung
        $this->assertStringContainsString('"name": "Wörthersee"', $json);
        $this->assertStringContainsString('"ticket_sales": "<p>Trafik</p>"', $json);
        $this->assertStringStartsWith("[\n    {", $json);

        $lake = json_decode($json, true)[0];
        $this->assertSame(['name', 'is_default', 'states', 'desc', 'hint', 'fishing_rights', 'ticket_sales', 'fish'], array_keys($lake));
        $this->assertSame(['Kärnten'], $lake['states']);
        $this->assertSame(['Hecht'], $lake['fish']);
        $this->assertStringNotContainsString('Geheimfisch', $json);
    }

    public function test_zip_contains_all_seed_files(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP-Erweiterung zip fehlt.');
        }

        $response = $this->actingAs($this->admin)->get(route('admin.wiki-export.zip'));
        $response->assertOk();

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($response->baseResponse->getFile()->getPathname()));

        foreach (['states', 'associations', 'fish', 'lakes', 'rivers'] as $dataset) {
            $this->assertNotFalse($zip->locateName("data/{$dataset}.json"), "data/{$dataset}.json fehlt");
        }

        $fish = json_decode($zip->getFromName('data/fish.json'), true);
        $this->assertSame(['Hecht'], array_column($fish, 'name'));
        $zip->close();
    }

    public function test_unknown_datasets_and_regular_users_are_rejected(): void
    {
        $this->actingAs($this->admin)->get('/admin/wiki-export/users.json')->assertNotFound();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.wiki-export.dataset', 'fish'))
            ->assertRedirect();
    }
}
