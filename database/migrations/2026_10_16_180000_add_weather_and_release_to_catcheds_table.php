<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Verbleib des Fisches (für die Fangmeldung) und automatisch ermittelte Wetterdaten.
     */
    public function up(): void
    {
        Schema::table('catcheds', function (Blueprint $table) {
            // null = nicht angegeben, true = zurückgesetzt, false = entnommen
            $table->boolean('released')->nullable()->after('bait');

            $table->decimal('weather_temperature', 4, 1)->nullable();
            $table->decimal('weather_pressure', 6, 1)->nullable();
            $table->decimal('weather_pressure_trend', 4, 1)->nullable();
            $table->unsignedTinyInteger('weather_humidity')->nullable();
            $table->decimal('weather_wind_speed', 4, 1)->nullable();
            $table->unsignedSmallInteger('weather_wind_direction')->nullable();
            $table->decimal('weather_precipitation', 5, 1)->nullable();
            $table->string('weather_status', 20)->nullable();
            $table->timestamp('weather_fetched_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('catcheds', function (Blueprint $table) {
            $table->dropColumn([
                'released',
                'weather_temperature',
                'weather_pressure',
                'weather_pressure_trend',
                'weather_humidity',
                'weather_wind_speed',
                'weather_wind_direction',
                'weather_precipitation',
                'weather_status',
                'weather_fetched_at',
            ]);
        });
    }
};
