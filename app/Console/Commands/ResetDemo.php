<?php

namespace App\Console\Commands;

use Database\Seeders\DemoUserSeeder;
use Illuminate\Console\Command;
use Throwable;

/**
 * Setzt den öffentlichen Demo-Zugang auf den Ausgangszustand zurück (3 Beispielfänge, keine Spots).
 * Läuft jede Nacht um Mitternacht über den Scheduler, kann aber auch manuell gestartet werden.
 */
class ResetDemo extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Demo-Konto zurücksetzen: Fänge, Fotos und Spots löschen und Beispieldaten neu anlegen';

    public function handle(): int
    {
        try {
            $this->callSilently('db:seed', ['--class' => DemoUserSeeder::class, '--force' => true]);
        } catch (Throwable $e) {
            report($e);
            $this->error("Demo-Konto konnte nicht zurückgesetzt werden: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('Demo-Konto zurückgesetzt.');

        return self::SUCCESS;
    }
}
