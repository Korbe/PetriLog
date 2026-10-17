<?php

namespace Tests\Feature\Backup;

use App\Models\BackupRun;
use App\Services\Backup\BackupException;
use App\Services\Backup\BackupFiles;
use App\Services\Backup\BackupRunner;
use App\Services\Backup\RemoteBackupStore;
use App\Services\Backup\RetentionPolicy;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Übertragungslogik gegen ein lokales Dateisystem – die SFTP-Verbindung selbst nutzt dieselbe Flysystem-Schnittstelle.
 */
class RemoteBackupStoreTest extends TestCase
{
    use RefreshDatabase;

    private string $localDir;
    private string $remoteDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localDir = storage_path('framework/testing/backups-local');
        $this->remoteDir = storage_path('framework/testing/backups-remote');
        File::deleteDirectory($this->localDir);
        File::deleteDirectory($this->remoteDir);
        File::ensureDirectoryExists($this->remoteDir);

        config(['backup.local_path' => $this->localDir, 'backup.remote.enabled' => true]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->localDir);
        File::deleteDirectory($this->remoteDir);
        parent::tearDown();
    }

    private function fakeBackup(string $date, string $contents = 'backup-inhalt'): string
    {
        $path = BackupFiles::path("petrilog-backup-{$date}.zip");
        file_put_contents($path, $contents . $date);
        BackupFiles::writeChecksumFile($path, BackupFiles::sha256($path));

        return $path;
    }

    private function remoteDisk(): FilesystemAdapter
    {
        return Storage::build(['driver' => 'local', 'root' => $this->remoteDir, 'throw' => true]);
    }

    public function test_upload_verifies_checksum_and_leaves_no_partial_file(): void
    {
        $archive = $this->fakeBackup('2026-10-17_031500');
        $store = new RemoteBackupStore($this->remoteDisk());

        $result = $store->upload($archive);

        $this->assertTrue($result['uploaded']);
        $this->assertFileEquals($archive, "{$this->remoteDir}/petrilog-backup-2026-10-17_031500.zip");
        $this->assertFileDoesNotExist("{$this->remoteDir}/petrilog-backup-2026-10-17_031500.zip.part");
        $this->assertStringStartsWith(BackupFiles::sha256($archive), file_get_contents("{$this->remoteDir}/petrilog-backup-2026-10-17_031500.zip.sha256"));
        $this->assertStringContainsString('OK petrilog-backup-2026-10-17_031500.zip', file_get_contents("{$this->remoteDir}/petrilog-backup.log"));

        // Zweiter Lauf: bereits vollständig vorhanden → kein erneuter Upload
        $this->assertFalse($store->upload($archive)['uploaded']);
    }

    public function test_corrupted_upload_is_rejected_and_never_finalized(): void
    {
        $archive = $this->fakeBackup('2026-10-17_031500');
        $healthy = $this->remoteDisk();

        // Simuliert eine Leitung, die Daten verfälscht
        $corrupting = new class($healthy->getDriver(), $healthy->getAdapter(), $healthy->getConfig()) extends FilesystemAdapter {
            public function writeStream($path, $resource, array $options = [])
            {
                return $this->put($path, 'kaputt');
            }
        };

        try {
            (new RemoteBackupStore($corrupting))->upload($archive);
            $this->fail('Beschädigter Upload wurde nicht erkannt.');
        } catch (BackupException $e) {
            $this->assertSame(BackupException::TRANSFER, $e->getCode());
        }

        $this->assertFileDoesNotExist("{$this->remoteDir}/petrilog-backup-2026-10-17_031500.zip");
        $this->assertFileDoesNotExist("{$this->remoteDir}/petrilog-backup-2026-10-17_031500.zip.part");
    }

    public function test_remote_retention_deletes_backups_and_checksums(): void
    {
        $store = new RemoteBackupStore($this->remoteDisk());
        foreach (['2026-10-17_031500', '2026-10-16_031500', '2026-10-15_031500'] as $date) {
            $store->upload($this->fakeBackup($date));
        }

        $result = $store->prune(new RetentionPolicy(daily: 2, weekly: 0, monthly: 0));

        $this->assertSame(['petrilog-backup-2026-10-15_031500.zip'], $result['delete']);
        $this->assertFileDoesNotExist("{$this->remoteDir}/petrilog-backup-2026-10-15_031500.zip");
        $this->assertFileDoesNotExist("{$this->remoteDir}/petrilog-backup-2026-10-15_031500.zip.sha256");
        $this->assertFileExists("{$this->remoteDir}/petrilog-backup-2026-10-16_031500.zip");
    }

    public function test_local_backups_are_only_pruned_after_confirmed_transfer(): void
    {
        config(['backup.retention.local.keep_latest' => 1]);
        $this->fakeBackup('2026-10-17_031500');
        $this->fakeBackup('2026-10-16_031500');
        $this->fakeBackup('2026-10-15_031500');

        // Nur das Backup vom 15. ist bestätigt übertragen
        BackupRun::create(['action' => 'transfer', 'status' => 'success', 'file' => 'petrilog-backup-2026-10-15_031500.zip']);

        $deleted = app(BackupRunner::class)->pruneLocal();

        $this->assertSame(['petrilog-backup-2026-10-15_031500.zip'], $deleted);
        $this->assertFileExists(BackupFiles::path('petrilog-backup-2026-10-16_031500.zip'), 'nicht übertragen → bleibt');
        $this->assertFileExists(BackupFiles::path('petrilog-backup-2026-10-17_031500.zip'), 'neuestes → bleibt');
        $this->assertFileDoesNotExist(BackupFiles::checksumFile(BackupFiles::path('petrilog-backup-2026-10-15_031500.zip')));
    }

    public function test_backup_names_are_validated_against_path_traversal(): void
    {
        $this->expectException(BackupException::class);
        BackupFiles::safeName('../../.env');
    }
}
