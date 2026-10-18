<?php

namespace App\Jobs;

use App\Models\BackupRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * Führt einen Backup-Befehl aus der Weboberfläche im Hintergrund aus.
 * Die eigentliche Logik steckt in den Artisan-Befehlen – Cron und Weboberfläche nutzen denselben Weg.
 */
class RunBackupCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    // Erlaubte Befehle und Optionen, die die Weboberfläche setzen darf
    public const ALLOWED = [
        'backup:run' => ['--no-transfer'],
        'backup:verify' => ['--uploaded'],
        'backup:restore-test' => [],
        'backup:restore-production' => ['--confirm'],
    ];

    // Ein Backup kann dauern; parallele Läufe verhindert die Dateisperre im BackupRunner
    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        public string $command,
        public int $runId,
        public ?string $file = null,
        public array $options = [],
    ) {
    }

    public function handle(): void
    {
        if (! array_key_exists($this->command, self::ALLOWED)) {
            return;
        }

        $arguments = [
            '--trigger' => 'web',
            '--run-id' => $this->runId,
            '--no-interaction' => true,
            ...array_intersect_key($this->options, array_flip(self::ALLOWED[$this->command])),
        ];

        if ($this->file !== null && $this->command !== 'backup:run') {
            $arguments['file'] = $this->file;
        }

        if ($this->command === 'backup:restore-test') {
            // Weboberfläche: Test-Datenbank danach wieder entfernen, nichts liegen lassen
            $arguments['--drop'] = true;
        }

        if ($this->command === 'backup:restore-production') {
            // Website-Sperre statt `artisan down` – sonst würde der Queue-Worker selbst pausieren
            $arguments['--maintenance'] = 'flag';
        }

        Artisan::call($this->command, $arguments);
    }

    public function failed(?Throwable $exception): void
    {
        $run = BackupRun::find($this->runId);

        // Nur offene Einträge als fehlgeschlagen markieren – läuft der Befehl noch, meldet er selbst das Ergebnis
        if ($run && $run->status === 'queued') {
            $run->finish('failed', 'Hintergrundauftrag fehlgeschlagen: ' . ($exception?->getMessage() ?? 'unbekannt'));
        }
    }
}
