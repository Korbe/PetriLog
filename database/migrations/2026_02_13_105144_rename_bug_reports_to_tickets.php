<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabelle umbenennen
        Schema::rename('bug_reports', 'tickets');
    }

    public function down(): void
    {
        // Umgekehrte Umbenennung, falls Rollback
        Schema::rename('tickets', 'bug_reports');
    }
};
