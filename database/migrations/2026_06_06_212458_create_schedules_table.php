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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('valve_id')->constrained()->cascadeOnDelete();
            $table->json('days_of_week');
            $table->time('start_time');
            $table->unsignedSmallInteger('duration_minutes');
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();

            $table->index(['valve_id', 'is_enabled', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
