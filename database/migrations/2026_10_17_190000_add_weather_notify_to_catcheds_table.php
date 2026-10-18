<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * E-Mail an den Angler, sobald die Wetterdaten zum Fang vorliegen (Checkbox beim Eintragen).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catcheds', function (Blueprint $table) {
            $table->boolean('weather_notify')->default(false)->after('weather_fetched_at');
        });
    }

    public function down(): void
    {
        Schema::table('catcheds', function (Blueprint $table) {
            $table->dropColumn('weather_notify');
        });
    }
};
