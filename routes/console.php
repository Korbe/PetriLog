<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Statistikdaten (Seitenaufrufe, Mail-Protokoll) nach einem Jahr löschen
Schedule::command('model:prune', ['--model' => [\App\Models\PageView::class, \App\Models\MailLog::class, \App\Models\BackupRun::class]])->daily();

// Nächtliches Backup über den Laravel-Scheduler (Alternative: eigener Cronjob, siehe docs/backup.md).
// Nur aktiv mit BACKUP_SCHEDULE=true, damit nicht zwei Mechanismen gleichzeitig sichern.
if (config('backup.schedule.enabled')) {
    Schedule::command('backup:run --trigger=schedule')
        ->dailyAt(config('backup.schedule.time'))
        ->timezone('Europe/Vienna')
        ->withoutOverlapping(360)
        ->runInBackground();
}
