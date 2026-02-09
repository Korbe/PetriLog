<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use App\Models\User;
use App\Models\State;
use App\Models\Lake;
use App\Models\River;
use App\Models\Association;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

class StateControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_states_index_with_media(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $states = State::factory()->count(2)->create();

        foreach ($states as $state) {
            $state->addMediaFromUrl('https://placehold.co/600x400')
                ->preservingOriginal()
                ->toMediaCollection('state');
            $state->refresh();
        }

        $response = $this->actingAs($user)->get(route('app.states.index'));

        $response->assertStatus(200);

        $response->assertInertia(function (Assert $page) use ($states) {
            $page->component('States/Index');

            foreach ($states->values() as $index => $state) {
                $page->where("states.$index.id", $state->id)
                    ->where("states.$index.name", $state->name)
                    ->where("states.$index.desc", $state->desc)
                    ->has("states.$index.media", 1, function (Assert $media) use ($state) {
                        $media->where('url', $state->getFirstMediaUrl('state'));
                    });
            }
        });
    }

    public function test_authenticated_user_can_view_single_state_with_relations(): void
    {
        // 🔹 User
        $user = User::factory()->create();

        // 🔹 State
        $state = State::factory()->create();

        // 🔹 Lakes (n:n)
        $lakes = Lake::factory()->count(2)->create();
        $state->lakes()->sync($lakes->pluck('id'));

        // 🔹 Rivers (n:n)
        $rivers = River::factory()->count(2)->create();
        $state->rivers()->sync($rivers->pluck('id'));

        // 🔹 Associations (HasMany)
        $associations = Association::factory()->count(2)->make();
        foreach ($associations as $assoc) {
            $assoc->state()->associate($state);
            $assoc->save();
        }

        // 🔹 Request als eingeloggter User
        $response = $this->actingAs($user)->get(route('app.states.show', $state));

        // 🔹 Status prüfen
        $response->assertStatus(200);

        // 🔹 Inertia Assertions
        $response->assertInertia(function (AssertableInertia $page) use ($state, $lakes, $rivers, $associations) {
            $page->component('States/Show');

            // 🔹 State-Daten prüfen
            $page->where('state.id', $state->id)
                ->where('state.name', $state->name)
                ->where('state.desc', $state->desc);

            // 🔹 Lakes prüfen
            foreach ($lakes->values() as $index => $lake) {
                $page->where("state.lakes.$index.id", $lake->id)
                    ->where("state.lakes.$index.name", $lake->name);
            }

            // 🔹 Rivers prüfen
            foreach ($rivers->values() as $index => $river) {
                $page->where("state.rivers.$index.id", $river->id)
                    ->where("state.rivers.$index.name", $river->name);
            }

            // 🔹 Associations prüfen
            foreach ($associations->values() as $index => $assoc) {
                $page->where("state.associations.$index.id", $assoc->id)
                    ->where("state.associations.$index.name", $assoc->name)
                    ->where("state.associations.$index.link", $assoc->link)
                    ->where("state.associations.$index.desc", $assoc->desc);
            }
        });
    }

    public function test_guest_is_redirected_to_login_for_state_routes(): void
    {
        $state = State::factory()->create();

        $this->get(route('app.states.index'))->assertRedirect(route('login'));
        $this->get(route('app.states.show', $state))->assertRedirect(route('login'));
    }
}
