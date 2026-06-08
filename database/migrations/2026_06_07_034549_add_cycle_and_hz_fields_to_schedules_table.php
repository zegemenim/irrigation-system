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
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('mode', 16)->default('weekly')->after('valve_id')->index();
            $table->json('cycle_valve_order')->nullable()->after('days_of_week');
            $table->date('cycle_start_date')->nullable()->after('cycle_valve_order');
            $table->unsignedTinyInteger('cycle_interval_days')->default(1)->after('cycle_start_date');
            $table->float('target_hz')->nullable()->after('duration_minutes');

            $table->index(['mode', 'is_enabled', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['mode', 'is_enabled', 'start_time']);
            $table->dropColumn([
                'mode',
                'cycle_valve_order',
                'cycle_start_date',
                'cycle_interval_days',
                'target_hz',
            ]);
        });
    }
};
