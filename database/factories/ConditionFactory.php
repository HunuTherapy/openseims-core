<?php

namespace Database\Factories;

use App\Models\Condition;
use App\Models\ConditionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Condition>
 */
class ConditionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('CND-###')),
            'name' => $this->faker->words(2, true),
            'category_id' => ConditionCategory::factory(),
            'description' => $this->faker->sentence(),
        ];
    }
}
