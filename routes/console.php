<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Statistikdaten (Seitenaufrufe, Mail-Protokoll) nach einem Jahr löschen
Schedule::command('model:prune', ['--model' => [\App\Models\PageView::class, \App\Models\MailLog::class]])->daily();
