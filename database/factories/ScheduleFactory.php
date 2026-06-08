<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\Valve;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'valve_id' => Valve::factory(),
            'mode' => 'weekly',
            'days_of_week' => ['monday', 'wednesday', 'friday'],
            'cycle_valve_order' => null,
            'cycle_start_date' => null,
            'cycle_interval_days' => 1,
            'start_time' => '07:00',
            'duration_minutes' => 30,
            'target_hz' => null,
            'is_enabled' => true,
        ];
    }
}
