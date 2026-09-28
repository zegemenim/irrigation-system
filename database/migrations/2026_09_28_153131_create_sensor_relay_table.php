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
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('sensor_number')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('sensor_relay', function (Blueprint $table) {
            $table->foreignId('sensor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('relay_id')->constrained()->cascadeOnDelete();
            $table->primary(['sensor_id', 'relay_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_relay');
        Schema::dropIfExists('sensors');
    }
};
