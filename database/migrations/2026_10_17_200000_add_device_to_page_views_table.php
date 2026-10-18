<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Geräteverteilung in der eigenen Statistik: Handy / Tablet / Desktop und ob PetriLog als App installiert läuft.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->string('device', 10)->nullable()->after('authenticated');
            $table->boolean('standalone')->default(false)->after('device');
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->dropColumn(['device', 'standalone']);
        });
    }
};
