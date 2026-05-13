<?php

namespace Database\Factories;

use App\Models\AssessmentForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentForm>
 */
class AssessmentFormFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
            'version' => $this->faker->numerify('v#.#'),
            'active' => true,
            'responses_count' => 0,
        ];
    }
}
