<?php

namespace Database\Factories;

use App\Models\AssessmentCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentCenter>
 */
class AssessmentCenterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'code' => strtoupper($this->faker->bothify('AC-###')),
            'address' => $this->faker->streetAddress(),
            'phone' => '+233'.$this->faker->numerify('2#########'),
            'email' => $this->faker->safeEmail(),
        ];
    }
}
