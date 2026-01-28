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
        Schema::table('lakes', function (Blueprint $table) {
            $table->string('fishing_rights')->nullable()->after('desc');
            $table->string('ticket_sales')->nullable()->after('fishing_rights');    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lakes', function (Blueprint $table) {
            $table->dropColumn('fishing_rights');
            $table->dropColumn('ticket_sales');
        });
    }
};
