<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Eigene, cookielose Zugriffsstatistik und Protokoll verschickter E-Mails für das Admin-Dashboard.
     */
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('route', 100)->nullable();
            $table->string('path', 191);
            // Täglich wechselnder Hash aus IP + Browser – keine IP-Adresse, kein Cookie, nicht tagesübergreifend verknüpfbar
            $table->char('visitor', 16);
            $table->boolean('authenticated')->default(false);
            $table->string('referrer_host', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['date', 'visitor']);
            $table->index(['date', 'route']);
        });

        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 80);
            $table->string('subject')->nullable();
            $table->string('recipient');
            $table->timestamp('sent_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('mail_logs');
    }
};
