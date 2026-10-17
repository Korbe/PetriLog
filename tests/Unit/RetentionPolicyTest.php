<?php

namespace Tests\Unit;

use App\Services\Backup\RetentionPolicy;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class RetentionPolicyTest extends TestCase
{
    /**
     * Ein Backup pro Tag über 120 Tage.
     */
    private function dailyBackups(int $days): array
    {
        $backups = [];
        $start = CarbonImmutable::parse('2026-10-17 03:15:00', 'UTC');

        for ($i = 0; $i < $days; $i++) {
            $date = $start->subDays($i);
            $backups['petrilog-backup-' . $date->format('Y-m-d_His') . '.zip'] = $date;
        }

        return $backups;
    }

    public function test_keeps_daily_weekly_and_monthly_backups(): void
    {
        $result = (new RetentionPolicy(daily: 7, weekly: 4, monthly: 3))->apply($this->dailyBackups(120));

        // 7 Tage + ältere Wochen + ältere Monate (Überschneidungen zählen einmal)
        $this->assertContains('petrilog-backup-2026-10-17_031500.zip', $result['keep']);
        $this->assertContains('petrilog-backup-2026-10-11_031500.zip', $result['keep']);
        $this->assertNotContains('petrilog-backup-2026-10-10_031500.zip', $result['keep'], 'Samstag liegt in derselben Woche wie ein neueres Backup');
        $this->assertContains('petrilog-backup-2026-08-31_031500.zip', $result['keep'], 'neuestes Backup im August');
        $this->assertNotContains('petrilog-backup-2026-07-31_031500.zip', $result['keep'], 'nur 3 Monate');
        $this->assertLessThanOrEqual(7 + 4 + 3, count($result['keep']));
        $this->assertCount(120, [...$result['keep'], ...$result['delete']]);
    }

    public function test_newest_backup_is_always_kept(): void
    {
        $result = (new RetentionPolicy(0, 0, 0))->apply($this->dailyBackups(5));

        $this->assertSame(['petrilog-backup-2026-10-17_031500.zip'], $result['keep']);
        $this->assertCount(4, $result['delete']);
    }

    public function test_multiple_backups_per_day_keep_only_the_newest(): void
    {
        $backups = [
            'petrilog-backup-2026-10-17_031500.zip' => CarbonImmutable::parse('2026-10-17 03:15', 'UTC'),
            'petrilog-backup-2026-10-17_120000.zip' => CarbonImmutable::parse('2026-10-17 12:00', 'UTC'),
            'petrilog-backup-2026-10-16_031500.zip' => CarbonImmutable::parse('2026-10-16 03:15', 'UTC'),
        ];

        $result = (new RetentionPolicy(daily: 2, weekly: 0, monthly: 0))->apply($backups);

        $this->assertEqualsCanonicalizing(['petrilog-backup-2026-10-17_120000.zip', 'petrilog-backup-2026-10-16_031500.zip'], $result['keep']);
        $this->assertSame(['petrilog-backup-2026-10-17_031500.zip'], $result['delete']);
    }
}
