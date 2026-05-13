<?php

namespace Database\Factories;

use App\Enums\TeacherType;
use App\Models\School;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_no' => $this->faker->unique()->bothify('TCH-####'),
            'teacher_type' => TeacherType::CLASS_TEACHER,
            'school_id' => School::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'class' => $this->faker->randomElement(['KG1', 'KG2', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'JHS1', 'JHS2', 'JHS3']),
            'qualification' => $this->faker->jobTitle(),
            'special_education_background' => $this->faker->optional()->sentence(),
            'training_on_inclusion' => $this->faker->boolean(),
            'skills' => $this->faker->optional()->words(3, true),
            'other_qualifications' => $this->faker->optional()->sentence(),
            'in_service_trainings_attended' => $this->faker->optional()->numberBetween(0, 20),
            'sen_certified' => $this->faker->boolean(),
            'is_deployed' => $this->faker->boolean(),
        ];
    }
}
