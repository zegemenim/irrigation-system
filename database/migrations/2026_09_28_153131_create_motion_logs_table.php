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
        Schema::create('motion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained()->restrictOnDelete();
            $table->boolean('motion_detected')->default(false);
            $table->json('triggered_relay_ids')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motion_logs');
    }
};
