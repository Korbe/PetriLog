<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use App\Models\Fish;
use App\Models\User;
use App\Models\River;
use App\Models\State;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RiverControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_river_page_displays_river_and_fish_data(): void
    {
        // 🔹 User (Route ist auth-geschützt)
        $user = User::factory()->create();

        // 🔹 State
        $state = State::factory()->create(['name' => 'Bayern']);

        // 🔹 River
        $river = River::factory()->create([
            'name' => 'Donau',
            'desc' => 'Großer Fluss',
            'hint' => 'Starke Strömung',
            'fishing_rights' => 'Erlaubnisschein notwendig',
            'ticket_sales' => 'Online',
        ]);

        // 🔹 n:n Pivot: River ↔ State
        $river->states()->attach($state->id);

        // 🔹 Fische
        $fish1 = Fish::factory()->create(['name' => 'Wels']);
        $fish2 = Fish::factory()->create(['name' => 'Zander']);
        $river->fish()->sync([$fish1->id, $fish2->id]);

        // 🔹 Request
        $response = $this->actingAs($user)->get(route('app.rivers.show', [
            'state' => $state->id,
            'river' => $river->id,
        ]));

        // 🔹 Status
        $response->assertStatus(200);

        // 🔹 Session meta wurde gelöscht
        $this->assertFalse(session()->has('meta'));

        // 🔹 Inertia Assertions
        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('Rivers/Show')
                ->where('state_id', $state->id)
                ->where('river.id', $river->id)
                ->where('river.name', 'Donau')
                ->where('river.desc', 'Großer Fluss')
                ->where('river.hint', 'Starke Strömung')
                ->where('river.fishing_rights', 'Erlaubnisschein notwendig')
                ->where('river.ticket_sales', 'Online')
                ->where('river.fish', [
                    ['id' => $fish1->id, 'name' => 'Wels'],
                    ['id' => $fish2->id, 'name' => 'Zander'],
                ])
                ->etc()
        );
    }
}
