<?php

namespace Tests\Feature\Controller;

use App\Models\Spot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SpotControllerTest extends TestCase
{
    use RefreshDatabase;

    private function spotFor(User $user, string $name = 'Schilfkante'): Spot
    {
        return $user->spots()->create(['name' => $name, 'latitude' => 46.58, 'longitude' => 13.92]);
    }

    public function test_index_shows_only_own_spots(): void
    {
        $user = User::factory()->create();
        $this->spotFor($user, 'Meiner');
        $this->spotFor(User::factory()->create(), 'Fremder');

        $this->actingAs($user)
            ->get(route('app.spots.index'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Spots/Index')
                ->has('spots', 1)
                ->where('spots.0.name', 'Meiner'));
    }

    public function test_spot_can_be_created_updated_and_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('app.spots.store'), [
            'name' => 'Steg Ost', 'note' => 'Früh morgens', 'latitude' => 46.6, 'longitude' => 13.9,
        ])->assertRedirect();

        $spot = $user->spots()->sole();
        $this->assertSame('Steg Ost', $spot->name);

        $this->actingAs($user)->put(route('app.spots.update', $spot), [
            'name' => 'Steg West', 'latitude' => 46.6, 'longitude' => 13.9,
        ])->assertRedirect();
        $this->assertSame('Steg West', $spot->fresh()->name);

        $this->actingAs($user)->delete(route('app.spots.destroy', $spot))->assertRedirect();
        $this->assertDatabaseMissing('spots', ['id' => $spot->id]);
    }

    public function test_validation_requires_name_and_valid_coordinates(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('app.spots.store'), ['name' => '', 'latitude' => 120, 'longitude' => 13.9])
            ->assertSessionHasErrors(['name', 'latitude']);
    }

    public function test_foreign_spots_cannot_be_changed_even_by_admins(): void
    {
        $spot = $this->spotFor(User::factory()->create());
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('app.spots.update', $spot), ['name' => 'Gekapert', 'latitude' => 1, 'longitude' => 1])
            ->assertForbidden();
        $this->actingAs($admin)->delete(route('app.spots.destroy', $spot))->assertForbidden();

        $this->assertSame('Schilfkante', $spot->fresh()->name);
    }
}
