<?php

namespace Database\Factories;

use App\Models\ConditionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConditionCategory>
 */
class ConditionCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('CC-###')),
            'name' => $this->faker->word(),
        ];
    }
}
