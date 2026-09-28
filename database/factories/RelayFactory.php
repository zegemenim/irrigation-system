<?php

namespace Database\Factories;

use App\Models\Relay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Relay>
 */
class RelayFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'relay_number' => $this->faker->unique()->numberBetween(1, 8),
            'name' => 'Projektör '.$this->faker->numberBetween(1, 8),
            'is_active' => false,
            'last_activated_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true, 'last_activated_at' => now()]);
    }
}
