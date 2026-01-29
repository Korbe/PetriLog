<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Lakes Tabelle
        Schema::table('lakes', function (Blueprint $table) {
            $table->text('fishing_rights')->nullable()->change();
            $table->text('ticket_sales')->nullable()->change();
        });

        // Rivers Tabelle
        Schema::table('rivers', function (Blueprint $table) {
            $table->text('fishing_rights')->nullable()->change();
            $table->text('ticket_sales')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Falls rollback nötig ist: zurück auf string(255)
        Schema::table('lakes', function (Blueprint $table) {
            $table->string('fishing_rights')->nullable()->change();
            $table->string('ticket_sales')->nullable()->change();
        });

        Schema::table('rivers', function (Blueprint $table) {
            $table->string('fishing_rights')->nullable()->change();
            $table->string('ticket_sales')->nullable()->change();
        });
    }
};
