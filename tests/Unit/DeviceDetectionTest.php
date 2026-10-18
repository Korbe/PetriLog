<?php

namespace Tests\Unit;

use App\Http\Middleware\TrackPageView;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DeviceDetectionTest extends TestCase
{
    public static function agents(): array
    {
        return [
            'iPhone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148', null, 'mobile'],
            'Android phone' => ['Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 Chrome/130.0 Mobile Safari/537.36', null, 'mobile'],
            'Android tablet' => ['Mozilla/5.0 (Linux; Android 14; SM-X710) AppleWebKit/537.36 Chrome/130.0 Safari/537.36', null, 'tablet'],
            'iPad (meldet sich als Mac)' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Safari/605.1.15', null, 'desktop'],
            'Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0 Safari/537.36', '?0', 'desktop'],
            'Client Hint mobil' => ['Mozilla/5.0 (Linux) Chrome/130.0', '?1', 'mobile'],
            'ohne User-Agent' => [null, null, 'desktop'],
        ];
    }

    #[DataProvider('agents')]
    public function test_device_is_classified(?string $ua, ?string $hint, string $expected): void
    {
        $this->assertSame($expected, TrackPageView::deviceFor($ua, $hint));
    }
}
