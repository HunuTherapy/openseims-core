<?php

namespace Database\Factories;

use App\Models\Learner;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Learner>
 */
class LearnerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'last_name' => $this->faker->lastName(),
            'sex' => $this->faker->randomElement(['M', 'F']),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
            'primary_contact_name' => $this->faker->name(),
            'primary_contact_phone' => '+233'.$this->faker->numerify('#########'),
            'secondary_contact_phone' => $this->faker->optional()->regexify('\\+233[0-9]{9}'),
            'primary_contact_email' => $this->faker->unique()->safeEmail(),
            'enrol_date' => $this->faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'class' => $this->faker->randomElement(['P1', 'P2', 'P3', 'P4', 'P5', 'P6']),
            'status' => 'enrolled',
            'specialist_visit_completed' => $this->faker->boolean(),
            'specific_needs' => $this->faker->sentence(),
        ];
    }
}
