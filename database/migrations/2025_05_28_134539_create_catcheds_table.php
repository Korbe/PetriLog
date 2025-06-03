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
        Schema::create('catcheds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('length');
            $table->integer('weight')->nullable();
            $table->integer('depth')->nullable();
            $table->integer('temperature')->nullable();
            $table->string('waters');
            $table->dateTime('date');
            $table->double('latitude');
            $table->double('longitude');
            $table->string('address')->nullable();;
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catcheds');
    }
};
