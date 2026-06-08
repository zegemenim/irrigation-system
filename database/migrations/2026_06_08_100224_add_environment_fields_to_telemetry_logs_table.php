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
        Schema::table('telemetry_logs', function (Blueprint $table) {
            $table->float('temperature_celsius')->nullable()->after('pressure_bar');
            $table->float('humidity_percent')->nullable()->after('temperature_celsius');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telemetry_logs', function (Blueprint $table) {
            $table->dropColumn([
                'temperature_celsius',
                'humidity_percent',
            ]);
        });
    }
};
