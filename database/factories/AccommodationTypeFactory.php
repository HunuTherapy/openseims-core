<?php

namespace Database\Factories;

use App\Models\AccommodationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccommodationType>
 */
class AccommodationTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('ACC-###')),
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
        ];
    }
}
