<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['fish', 'lakes', 'rivers'];

    /**
     * User können fehlende Fischarten und Gewässer vorschlagen. Bis zur Freigabe durch einen Admin
     * sind diese nur für den Einreichenden sichtbar. Bestehende Einträge gelten als freigegeben.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->boolean('is_approved')->default(true)->index();
                $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropConstrainedForeignId('submitted_by');
                $table->dropColumn('is_approved');
            });
        }
    }
};
