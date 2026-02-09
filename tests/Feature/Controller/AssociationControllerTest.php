<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use App\Models\User;
use App\Models\State;
use App\Models\Association;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssociationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_association_show_route(): void
    {
        $state = State::factory()->create();
        $association = Association::factory()->create([
            'state_id' => $state->id,
        ]);

        $response = $this->get(route('app.associations.show', [
            'state' => $state,
            'association' => $association,
        ]));

        $response->assertRedirect('/login');
    }

    public function test_association_show_page_is_rendered_with_correct_data(): void
    {
        // 🔹 User (Route ist auth-geschützt)
        $user = User::factory()->create();

        // 🔹 State & Association
        $state = State::factory()->create();

        $association = Association::factory()->create([
            'state_id' => $state->id,
            'name' => 'Angelverein Musterstadt',
            'desc' => 'Ein toller Verein',
            'link' => 'https://verein.test',
        ]);

        // 🔹 Session vorbereiten
        $this->withSession([
            'meta' => [
                'title' => 'Alte Meta Daten',
            ],
        ]);

        // 🔹 Request
        $response = $this
            ->actingAs($user)
            ->get(route('app.associations.show', [
                'state' => $state,
                'association' => $association,
            ]));

        // 🔹 Assertions
        $response->assertStatus(200);

        // 🔹 Session: meta wurde vergessen
        $this->assertFalse(session()->has('meta'));

        // 🔹 Inertia Assertions
        $response->assertInertia(
            fn(AssertableInertia $page) => $page
                ->component('Association/Show')
                ->where('state_id', $state->id)
                ->where('association.id', $association->id)
                ->where('association.name', 'Angelverein Musterstadt')
                ->where('association.desc', 'Ein toller Verein')
                ->where('association.link', 'https://verein.test')
                ->etc()
        );
    }
}
