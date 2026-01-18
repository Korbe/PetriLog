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
        Schema::table('catcheds', function (Blueprint $table) {
            // name entfernen
            $table->dropColumn('name');

            // fish_id hinzufügen
            $table->foreignId('fish_id')
                ->after('user_id')
                ->constrained('fish')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('catcheds', function (Blueprint $table) {
            $table->string('name');

            $table->dropForeign(['fish_id']);
            $table->dropColumn('fish_id');
        });
    }
};
