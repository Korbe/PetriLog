<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\User;
use App\Models\State;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LakeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_lake_page_displays_lake_and_fish_data(): void
    {
        $user = User::factory()->create();

        // 🔹 State erstellen
        $state = State::factory()->create(['name' => 'Bayern']);

        // 🔹 Lake erstellen
        $lake = Lake::factory()->create([
            'name' => 'Starnberger See',
            'desc' => 'Ein schöner See',
            'hint' => 'Nur tagsüber angeln',
            'fishing_rights' => 'Angelschein erforderlich',
            'ticket_sales' => 'Am Kiosk',
        ]);

        // 🔹 Pivot-Verknüpfung: Lake gehört zum State
        $lake->states()->attach($state->id);

        // 🔹 Fische für diesen Lake
        $fish1 = Fish::factory()->create(['name' => 'Karpfen']);
        $fish2 = Fish::factory()->create(['name' => 'Hecht']);
        $lake->fish()->sync([$fish1->id, $fish2->id]);

        // 🔹 Request (IDs übergeben für Route Model Binding)
        $response = $this->actingAs($user)->get(route('app.lakes.show', [
            'state' => $state->id,
            'lake'  => $lake->id,
        ]));

        // 🔹 Status prüfen
        $response->assertStatus(200);

        // 🔹 Session wurde geleert
        $this->assertFalse(session()->has('meta'));

        // 🔹 Inertia Assertions
        $response->assertInertia(
            fn(\Inertia\Testing\AssertableInertia $page) =>
            $page->component('Lakes/Show')
                ->where('state_id', $state->id)
                ->where('lake.id', $lake->id)
                ->where('lake.name', 'Starnberger See')
                ->where('lake.desc', 'Ein schöner See')
                ->where('lake.hint', 'Nur tagsüber angeln')
                ->where('lake.fishing_rights', 'Angelschein erforderlich')
                ->where('lake.ticket_sales', 'Am Kiosk')
                ->where('lake.fish', [
                    ['id' => $fish1->id, 'name' => 'Karpfen'],
                    ['id' => $fish2->id, 'name' => 'Hecht'],
                ])
                ->etc()
        );
    }
}
