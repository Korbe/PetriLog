<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wer hat einen Wiki-Eintrag (Fisch, See, Fluss, Bundesland, Verein) zuletzt bearbeitet – nur intern sichtbar.
 */
return new class extends Migration
{
    private const TABLES = ['fish', 'lakes', 'rivers', 'states', 'associations'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropConstrainedForeignId('updated_by');
            });
        }
    }
};
