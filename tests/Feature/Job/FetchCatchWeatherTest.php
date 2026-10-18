<?php

namespace Tests\Feature\Job;

use App\Jobs\FetchCatchWeather;
use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\User;
use App\Services\GeoSphereWeather;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Mail\WeatherReadyMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FetchCatchWeatherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.geosphere.enabled' => true]);
        Http::preventStrayRequests();
    }

    private function catchAt(string $date, float $lat = 46.58, float $lng = 13.92): Catched
    {
        return Catched::factory()->forUser(User::factory()->create())->create([
            'fish_id' => Fish::factory()->create()->id,
            'lake_id' => Lake::factory()->create()->id,
            'river_id' => null,
            'date' => $date,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }

    private function geosphereResponse(array $timestamps, array $overrides = []): array
    {
        return [
            'timestamps' => $timestamps,
            'features' => [[
                'properties' => ['parameters' => array_merge([
                    'T2M' => ['data' => [5.3, 5.32, 5.47, 8.26]],
                    'P0' => ['data' => [102977.34, 103031.71, 103052.54, 103209.56]],
                    'RH2M' => ['data' => [88.7, 93.4, 100.0, 94.2]],
                    'UU' => ['data' => [0.3, 0.8, 0.6, -3.0]],
                    'VV' => ['data' => [0.8, 0.9, 0.7, 0.0]],
                    'RR' => ['data' => [0.0, 0.2, 0.0, 1.1]],
                ], $overrides)],
            ]],
        ];
    }

    private function runJob(Catched $catched): void
    {
        (new FetchCatchWeather($catched->id))->handle(app(GeoSphereWeather::class));
    }

    public function test_it_stores_the_weather_of_the_catch_hour(): void
    {
        // 07:40 UTC → nächste volle Stunde 08:00, Rückblick ab 05:00
        $catched = $this->catchAt('2026-10-14 07:40:00');

        Http::fake(['dataset.api.hub.geosphere.at/*' => Http::response($this->geosphereResponse([
            '2026-10-14T05:00+00:00', '2026-10-14T06:00+00:00', '2026-10-14T07:00+00:00', '2026-10-14T08:00+00:00',
        ]))]);

        $this->runJob($catched);
        $catched->refresh();

        $this->assertSame('ok', $catched->weather_status);
        $this->assertSame(8.3, $catched->weather_temperature);
        $this->assertSame(1032.1, $catched->weather_pressure);
        $this->assertSame(2.3, $catched->weather_pressure_trend);
        $this->assertSame(94, $catched->weather_humidity);
        $this->assertSame(10.8, $catched->weather_wind_speed);
        // Wind aus Osten (u negativ = Wind weht nach Westen)
        $this->assertSame(90, $catched->weather_wind_direction);
        $this->assertSame(1.3, $catched->weather_precipitation);

        Http::assertSent(fn($request) => str_contains($request->url(), 'start=2026-10-14T05%3A00')
            && str_contains($request->url(), 'end=2026-10-14T08%3A00')
            && str_contains($request->url(), 'lat_lon=46.58%2C13.92'));
    }

    public function test_catches_outside_the_covered_area_are_marked_unavailable_without_a_request(): void
    {
        $catched = $this->catchAt('2026-10-14 07:00:00', 52.52, 13.40); // Berlin

        $this->runJob($catched);

        $this->assertSame('unavailable', $catched->fresh()->weather_status);
        Http::assertNothingSent();
    }

    public function test_missing_analysis_hour_is_retried_later_instead_of_saved(): void
    {
        $catched = $this->catchAt('2026-10-14 07:40:00');

        Http::fake(['dataset.api.hub.geosphere.at/*' => Http::response($this->geosphereResponse([
            '2026-10-14T05:00+00:00', '2026-10-14T06:00+00:00', '2026-10-14T07:00+00:00',
        ]))]);

        $this->runJob($catched);

        $this->assertNull($catched->fresh()->weather_status);
    }

    public function test_store_and_relevant_updates_queue_a_weather_fetch(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $payload = [
            'date' => '2026-05-01 06:30:00',
            'fish_id' => Fish::factory()->create()->id,
            'lake_id' => Lake::factory()->create()->id,
            'length' => 55,
            'latitude' => 47.8,
            'longitude' => 13.0,
            'released' => true,
        ];

        $this->actingAs($user)->post(route('app.catched.store'), $payload);
        $catched = Catched::where('user_id', $user->id)->sole();

        $this->assertTrue($catched->released);
        Queue::assertPushed(FetchCatchWeather::class, 1);

        $catched->forceFill(['weather_status' => 'ok', 'weather_temperature' => 12.5])->saveQuietly();

        // Nur die Länge geändert → Wetter bleibt
        $this->actingAs($user)->post(route('app.catched.update', $catched->id), [...$payload, 'length' => 60]);
        $this->assertSame('ok', $catched->fresh()->weather_status);
        Queue::assertPushed(FetchCatchWeather::class, 1);

        // Ort geändert → Wetter wird zurückgesetzt und neu geholt
        $this->actingAs($user)->post(route('app.catched.update', $catched->id), [...$payload, 'latitude' => 47.5]);
        $this->assertNull($catched->fresh()->weather_status);
        $this->assertNull($catched->fresh()->weather_temperature);
        Queue::assertPushed(FetchCatchWeather::class, 2);
    }

    private function fakeWeather(): void
    {
        Http::fake(['dataset.api.hub.geosphere.at/*' => Http::response($this->geosphereResponse([
            '2026-10-14T05:00+00:00', '2026-10-14T06:00+00:00', '2026-10-14T07:00+00:00', '2026-10-14T08:00+00:00',
        ]))]);
    }

    public function test_user_is_notified_once_when_weather_arrives_if_requested(): void
    {
        Mail::fake();
        $this->fakeWeather();

        $catched = $this->catchAt('2026-10-14 07:40:00');
        $catched->forceFill(['weather_notify' => true])->save();

        $this->runJob($catched);

        Mail::assertQueued(WeatherReadyMail::class, fn($mail) => $mail->hasTo($catched->user->email));
        $this->assertFalse($catched->fresh()->weather_notify);

        // Erneuter Abruf (z. B. nach Bearbeiten) → keine zweite Mail
        $this->runJob($catched);
        Mail::assertQueuedCount(1);
    }

    public function test_no_notification_without_request_or_without_data(): void
    {
        Mail::fake();
        $this->fakeWeather();

        $this->runJob($this->catchAt('2026-10-14 07:40:00'));

        $berlin = $this->catchAt('2026-10-14 07:00:00', 52.52, 13.40);
        $berlin->forceFill(['weather_notify' => true])->save();
        $this->runJob($berlin);

        Mail::assertNothingQueued();
        $this->assertFalse($berlin->fresh()->weather_notify);
    }

    public function test_weather_mail_renders_the_values(): void
    {
        $this->fakeWeather();
        $catched = $this->catchAt('2026-10-14 07:40:00');
        $this->runJob($catched);

        $html = (new WeatherReadyMail($catched->fresh()))->render();

        $this->assertStringContainsString('8,3 °C', $html);
        $this->assertStringContainsString('1.032 hPa · steigend', $html);
        $this->assertStringContainsString('11 km/h aus O', $html);
        $this->assertStringContainsString(route('app.catched.show', $catched->id), $html);
    }

    public function test_checkbox_is_stored_when_creating_a_catch(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('app.catched.store'), [
            'date' => now()->toIso8601String(),
            'fish_id' => Fish::factory()->create()->id,
            'lake_id' => Lake::factory()->create()->id,
            'length' => 50,
            'latitude' => 46.6,
            'longitude' => 13.9,
            'weather_notify' => true,
        ])->assertSessionHasNoErrors();

        $this->assertTrue($user->catched()->first()->weather_notify);
    }
}
