<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\Learner;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    public function definition(): array
    {
        $present = $this->faker->boolean();

        return [
            'teacher_id' => Teacher::factory(),
            'learner_id' => Learner::factory(),
            'class' => $this->faker->randomElement(['KG1', 'KG2', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'JHS1', 'JHS2', 'JHS3']),
            'date' => $this->faker->date(),
            'present' => $present,
            'reason' => $present ? null : $this->faker->sentence(),
        ];
    }
}
