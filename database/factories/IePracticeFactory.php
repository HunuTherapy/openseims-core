<?php

namespace Database\Factories;

use App\Models\IePractice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IePractice>
 */
class IePracticeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
        ];
    }
}
