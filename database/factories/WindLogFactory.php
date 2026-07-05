<?php

namespace Database\Factories;

use App\Models\WindLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WindLog>
 */
class WindLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $windSpeed = $this->faker->randomFloat(2, 0, 16);

        return [
            'wind_speed' => $windSpeed,
            'generated_power' => $this->generatedPower($windSpeed),
        ];
    }

    private function generatedPower(float $windSpeed): float
    {
        if ($windSpeed < 2.5) {
            return 0.0;
        }

        if ($windSpeed > 12.0) {
            return 500.0;
        }

        return 500 * pow(($windSpeed - 2.5) / 9.5, 3);
    }
}
