<?php

namespace Database\Factories;

use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sensor>
 */
class SensorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $number = 0;
        $number++;

        return [
            'sensor_number' => $number,
            'name' => "Hareket Sensörü {$number}",
        ];
    }
}
