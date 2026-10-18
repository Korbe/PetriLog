<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fänge sind nur noch über einen zufälligen Freigabe-Link öffentlich – nicht mehr über die fortlaufende ID.
 * Bestehende Fänge sind danach privat, bis ihr Besitzer sie (erneut) teilt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catcheds', function (Blueprint $table) {
            $table->string('share_token', 40)->nullable()->unique()->after('user_id');
            $table->timestamp('shared_at')->nullable()->after('share_token');
        });
    }

    public function down(): void
    {
        Schema::table('catcheds', function (Blueprint $table) {
            $table->dropUnique(['share_token']);
            $table->dropColumn(['share_token', 'shared_at']);
        });
    }
};
