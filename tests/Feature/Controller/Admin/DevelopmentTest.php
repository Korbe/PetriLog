<?php

namespace Tests\Feature\Controller\Admin;

use App\Jobs\RunBackupCommand;
use App\Models\BackupRun;
use App\Models\User;
use App\Services\Backup\BackupFiles;
use App\Services\Backup\RestoreMaintenance;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DevelopmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $backupDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->backupDir = storage_path('framework/testing/backups-ui');
        File::deleteDirectory($this->backupDir);
        config(['backup.local_path' => $this->backupDir]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupDir);
        parent::tearDown();
    }

    /**
     * Admin-Sitzung mit frischer Passwort-Bestätigung (Backup-Bereich verlangt sie).
     */
    private function confirmed(): static
    {
        return $this->actingAs($this->admin)->withSession(['auth.password_confirmed_at' => time()]);
    }

    public function test_backup_area_requires_password_confirmation(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.development.backups.index'))
            ->assertRedirect(route('password.confirm'));
    }

    public function test_backups_can_be_uploaded_in_chunks_and_are_verified_afterwards(): void
    {
        Queue::fake();
        $name = 'petrilog-backup-2026-10-01_031500.zip';
        $uploadId = '9b2f7c1e-1c5e-4c1f-9f6e-2a7d1f0e8a11';

        foreach (['erster-teil-', 'zweiter-teil'] as $index => $part) {
            $this->confirmed()
                ->post(route('admin.development.backups.upload'), [
                    'upload_id' => $uploadId, 'name' => $name, 'index' => $index, 'total' => 2,
                    'chunk' => UploadedFile::fake()->createWithContent('chunk', $part),
                ], ['Accept' => 'application/json'])
                ->assertOk();
        }

        $this->assertSame('erster-teil-zweiter-teil', file_get_contents(BackupFiles::path($name)));
        $this->assertEmpty(glob($this->backupDir . '/.upload-*'), 'Keine Teil-Dateien übrig');

        $run = BackupRun::where('action', 'verify')->sole();
        $this->assertSame('queued', $run->status);
        Queue::assertPushed(RunBackupCommand::class, fn($job) => $job->command === 'backup:verify'
            && $job->file === $name && ($job->options['--uploaded'] ?? false));
    }

    public function test_upload_rejects_foreign_files_and_out_of_order_chunks(): void
    {
        $chunk = fn() => UploadedFile::fake()->createWithContent('chunk', 'x');

        $this->confirmed()->post(route('admin.development.backups.upload'), [
            'upload_id' => '9b2f7c1e-1c5e-4c1f-9f6e-2a7d1f0e8a12', 'name' => 'datenbank.sql', 'index' => 0, 'total' => 1, 'chunk' => $chunk(),
        ], ['Accept' => 'application/json'])->assertJsonValidationErrors('name');

        $this->confirmed()->post(route('admin.development.backups.upload'), [
            'upload_id' => '9b2f7c1e-1c5e-4c1f-9f6e-2a7d1f0e8a13', 'name' => 'petrilog-backup-2026-10-01_031500.zip', 'index' => 3, 'total' => 5, 'chunk' => $chunk(),
        ], ['Accept' => 'application/json'])->assertStatus(409);
    }

    public function test_production_restore_requires_the_database_name_and_is_queued(): void
    {
        Queue::fake();
        $name = $this->fakeBackup();
        $database = config('database.connections.' . config('backup.database.connection') . '.database');

        $this->confirmed()
            ->post(route('admin.development.backups.restore-production', $name), ['confirm' => 'falsch', 'acknowledge' => true])
            ->assertSessionHasErrors('confirm');
        Queue::assertNothingPushed();

        $this->confirmed()
            ->post(route('admin.development.backups.restore-production', $name), ['confirm' => 'WIEDERHERSTELLEN', 'acknowledge' => true])
            ->assertSessionHas('success');

        Queue::assertPushed(RunBackupCommand::class, fn($job) => $job->command === 'backup:restore-production'
            && $job->file === $name && $job->options === ['--confirm' => $database]);
    }

    public function test_website_is_locked_while_a_production_restore_runs(): void
    {
        RestoreMaintenance::enable('petrilog-backup-2026-10-17_031500.zip');

        try {
            $this->get('/')->assertStatus(503)->assertSee('wiederhergestellt');
            $this->actingAs($this->admin)->get('/admin')->assertStatus(503);
        } finally {
            RestoreMaintenance::disable();
        }

        $this->get('/')->assertOk();
    }

    private function fakeBackup(): string
    {
        $path = BackupFiles::path('petrilog-backup-2026-10-17_031500.zip');
        file_put_contents($path, 'zip');
        BackupFiles::writeChecksumFile($path, BackupFiles::sha256($path));

        return basename($path);
    }

    public function test_development_area_lists_backups_wiki_export_and_pulse(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.development.index'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Admin/Development/Index')
                ->where('groups', function ($groups) {
                    $keys = collect($groups)->pluck('tools')->flatten(1)->pluck('key')->all();
                    return in_array('backups', $keys) && in_array('wiki-export', $keys) && in_array('pulse', $keys);
                }));

        // Wiki-Export ist unter Development erreichbar, die alte Adresse leitet weiter
        $this->actingAs($this->admin)->get(route('admin.development.wiki-export.index'))->assertOk();
        $this->actingAs($this->admin)->get('/admin/wiki-export')->assertRedirect('/admin/development/wiki-export');
    }

    public function test_non_admins_cannot_reach_any_development_tool(): void
    {
        $user = User::factory()->create();
        $name = $this->fakeBackup();

        // Gäste landen beim Login
        $this->get(route('admin.development.backups.index'))->assertRedirect('/login');

        foreach ([
            route('admin.development.index'),
            route('admin.development.backups.index'),
            route('admin.development.wiki-export.index'),
            route('admin.development.backups.download', $name),
        ] as $url) {
            $this->actingAs($user)->get($url)->assertRedirect('/dashboard');
        }

        $this->actingAs($user)->post(route('admin.development.backups.store'))->assertRedirect('/dashboard');
        $this->assertSame(0, BackupRun::count());
    }

    public function test_backup_page_shows_local_backups(): void
    {
        $name = $this->fakeBackup();

        $this->confirmed()
            ->get(route('admin.development.backups.index'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Admin/Development/Backups')
                ->where('backups.0.name', $name)
                ->where('backups.0.transferred', false)
                ->has('config.retention'));
    }

    public function test_creating_a_backup_is_queued_and_tracked(): void
    {
        Queue::fake();

        $this->confirmed()->post(route('admin.development.backups.store'))->assertRedirect();

        $run = BackupRun::sole();
        $this->assertSame('queued', $run->status);
        $this->assertSame('web', $run->trigger);
        $this->assertSame($this->admin->id, $run->user_id);
        // Knopf in der Weboberfläche = nur zum Herunterladen, keine Übertragung zum Backup-Server
        Queue::assertPushed(RunBackupCommand::class, fn($job) => $job->command === 'backup:run' && $job->runId === $run->id
            && ($job->options['--no-transfer'] ?? false) === true);

        // Kein zweiter Auftrag, solange einer läuft
        $this->confirmed()->post(route('admin.development.backups.store'))->assertSessionHas('error');
        Queue::assertPushed(RunBackupCommand::class, 1);
    }

    public function test_download_requires_password_confirmation_and_valid_names(): void
    {
        $name = $this->fakeBackup();

        // Ohne frische Passwort-Bestätigung → Bestätigungsseite
        $this->actingAs($this->admin)
            ->get(route('admin.development.backups.download', $name))
            ->assertRedirect(route('password.confirm'));

        $this->actingAs($this->admin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('admin.development.backups.download', $name))
            ->assertOk()
            ->assertDownload($name);

        $this->actingAs($this->admin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get('/admin/development/backups/..%2F..%2F.env/download')
            ->assertNotFound();
    }

    public function test_backup_directory_is_not_inside_the_public_webroot(): void
    {
        $this->assertStringStartsNotWith(realpath(public_path()), realpath(BackupFiles::directory()));
        $this->assertStringStartsNotWith(public_path(), config('backup.local_path'));
    }
}
