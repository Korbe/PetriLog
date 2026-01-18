<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catcheds', function (Blueprint $table) {
            $table->dropColumn('waters');
            $table->foreignId('lake_id')->after('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('river_id')->after('lake_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('catcheds', function (Blueprint $table) {
            $table->string('waters')->after('temperature');
            $table->dropForeign(['lake_id']);
            $table->dropColumn('lake_id');
            $table->dropForeign(['river_id']);
            $table->dropColumn('river_id');
        });
    }
};
