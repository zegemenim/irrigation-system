<?php

namespace Database\Factories;

use App\Models\TelemetryLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TelemetryLog>
 */
class TelemetryLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pressure_bar' => fake()->randomFloat(2, 1, 5),
            'temperature_celsius' => fake()->randomFloat(1, 5, 42),
            'humidity_percent' => fake()->randomFloat(1, 10, 95),
            'inverter_hz' => fake()->randomFloat(2, 0, 50),
            'inverter_status' => 'RUN',
            'inverter_current' => fake()->randomFloat(2, 0, 12),
            'error_code' => 0,
        ];
    }
}
