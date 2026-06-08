<?php

namespace Database\Factories;

use App\Models\Valve;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Valve>
 */
class ValveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'valve_number' => fake()->numberBetween(1, (int) config('irrigation.valve_count', 4)),
            'name' => fake()->words(2, true),
            'is_active' => false,
            'last_activated_at' => null,
        ];
    }
}
