<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeocodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_reverse_geocoding_returns_a_short_address_and_is_cached(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response([
            'display_name' => 'lang …',
            'address' => ['road' => 'Seeweg', 'house_number' => '3', 'village' => 'Drobollach', 'postcode' => '9580', 'country' => 'Österreich'],
        ])]);

        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('app.geocode.reverse', ['lat' => 46.581234, 'lng' => 13.912345]))
            ->assertOk()->assertJson(['address' => 'Seeweg 3, 9580 Drobollach, Österreich']);

        // Gleiche Stelle (gerundet) → aus dem Cache, kein zweiter Aufruf bei Nominatim
        $this->actingAs($user)->getJson(route('app.geocode.reverse', ['lat' => 46.581239, 'lng' => 13.912341]))->assertOk();

        Http::assertSentCount(1);
        Http::assertSent(fn($request) => str_contains($request->header('User-Agent')[0], 'PetriLog')
            && $request['lat'] == 46.5812 && $request['lon'] == 13.9123);
    }

    public function test_failures_return_no_address(): void
    {
        Http::fake(['*' => Http::response('', 500)]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('app.geocode.reverse', ['lat' => 47, 'lng' => 13]))
            ->assertOk()->assertJson(['address' => null]);
    }

    public function test_guests_and_invalid_coordinates_are_rejected(): void
    {
        $this->getJson(route('app.geocode.reverse', ['lat' => 47, 'lng' => 13]))->assertUnauthorized();
        $this->actingAs(User::factory()->create())
            ->getJson(route('app.geocode.reverse', ['lat' => 200, 'lng' => 13]))->assertUnprocessable();
    }
}
