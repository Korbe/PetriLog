<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Protokoll aller Backup-Vorgänge (Erstellen, Prüfen, Übertragen, Wiederherstellungstest, Aufräumen).
     */
    public function up(): void
    {
        Schema::create('backup_runs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 30);           // create | verify | transfer | restore-test | prune
            $table->string('status', 20);           // queued | running | success | warning | failed
            $table->string('trigger', 20)->default('cli'); // cli | schedule | web
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file')->nullable();     // Dateiname des Backups (ohne Pfad)
            $table->unsignedBigInteger('size')->nullable();
            $table->char('sha256', 64)->nullable();
            $table->text('message')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['action', 'status']);
            $table->index('file');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_runs');
    }
};
