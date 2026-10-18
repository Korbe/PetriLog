<?php

namespace Tests\Feature\Backup;

use App\Models\BackupRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BackupStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['backup.local_path' => storage_path('framework/testing/backups-status'), 'backup.remote.enabled' => true]);
        \Illuminate\Support\Facades\File::ensureDirectoryExists(config('backup.local_path'));
    }

    public function test_status_is_ok_with_a_recent_transfer_and_cleans_up_crashed_runs(): void
    {
        BackupRun::create(['action' => 'transfer', 'status' => 'success', 'file' => 'x.zip', 'finished_at' => now()->subHours(3)]);
        $crashed = BackupRun::create(['action' => 'create', 'status' => 'running', 'started_at' => now()->subMinutes(10)]);

        $this->artisan('backup:status')->expectsOutputToContain('Alles in Ordnung')->assertSuccessful();

        $this->assertSame('failed', $crashed->fresh()->status);
    }

    public function test_status_warns_when_the_last_backup_is_too_old(): void
    {
        BackupRun::create(['action' => 'transfer', 'status' => 'success', 'file' => 'x.zip', 'finished_at' => now()->subHours(40)]);

        $this->artisan('backup:status')->expectsOutputToContain('läuft der Cron?')->assertFailed();
    }

    public function test_admin_dashboard_warns_about_a_missing_backup(): void
    {
        BackupRun::create(['action' => 'transfer', 'status' => 'success', 'file' => 'x.zip', 'finished_at' => now()->subHours(40)]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.index'))
            ->assertInertia(fn(Assert $page) => $page->where('stats.health.backupAgeHours', 40));
    }

    public function test_download_copies_do_not_count_as_backups_without_second_server(): void
    {
        config(['backup.remote.enabled' => false]);
        BackupRun::create(['action' => 'create', 'status' => 'success', 'file' => 'a.zip', 'finished_at' => now()->subHour(),
            'details' => ['download_only' => true]]);

        $this->assertNull(BackupRun::lastSuccessfulBackupAt());
    }
}
