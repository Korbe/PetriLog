<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Prüfen, ob die Spalte existiert und umbenennen
            if (Schema::hasColumn('users', 'isAdmin')) {
                $table->renameColumn('isAdmin', 'is_admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rückgängig machen
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->renameColumn('is_admin', 'isAdmin');
            }
        });
    }
};
