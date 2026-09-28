<?php

namespace Database\Factories;

use App\Models\MotionLog;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MotionLog>
 */
class MotionLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sensor_id' => Sensor::factory(),
            'motion_detected' => $this->faker->boolean(70),
            'triggered_relay_ids' => null,
        ];
    }

    public function withMotion(): static
    {
        return $this->state([
            'motion_detected' => true,
            'triggered_relay_ids' => [1, 2],
        ]);
    }
}
