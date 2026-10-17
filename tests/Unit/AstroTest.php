<?php

namespace Tests\Unit;

use App\Support\Astro;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class AstroTest extends TestCase
{
    public function test_moon_phases_match_known_dates(): void
    {
        // Vollmond 23.04.2024 23:49 UTC, Neumond 08.04.2024 18:21 UTC
        $full = Astro::moon(Carbon::parse('2024-04-23 23:49:00', 'UTC'));
        $new = Astro::moon(Carbon::parse('2024-04-08 18:21:00', 'UTC'));
        $firstQuarter = Astro::moon(Carbon::parse('2024-04-15 19:13:00', 'UTC'));

        $this->assertSame('Vollmond', $full['name']);
        $this->assertGreaterThanOrEqual(99, $full['illumination']);
        $this->assertSame('Neumond', $new['name']);
        $this->assertLessThanOrEqual(1, $new['illumination']);
        $this->assertSame('Erstes Viertel', $firstQuarter['name']);
        $this->assertTrue($firstQuarter['waxing']);
    }

    public function test_sun_times_and_daytime_in_vienna(): void
    {
        // Sommersonnenwende in Wien: Aufgang ca. 04:53, Untergang ca. 20:58 (MESZ) – wenige Minuten Toleranz
        $sun = Astro::sun(Carbon::parse('2024-06-21 03:15:00', 'UTC'), 48.21, 16.37);

        $this->assertMatchesRegularExpression('/^04:5\d$/', $sun['sunrise']);
        $this->assertMatchesRegularExpression('/^(20:5\d|21:0[0-3])$/', $sun['sunset']);
        $this->assertSame('Morgendämmerung', $sun['daytime']);

        $this->assertSame('Tag', Astro::sun(Carbon::parse('2024-06-21 11:00:00', 'UTC'), 48.21, 16.37)['daytime']);
        $this->assertSame('Nacht', Astro::sun(Carbon::parse('2024-06-21 23:30:00', 'UTC'), 48.21, 16.37)['daytime']);
    }
}
