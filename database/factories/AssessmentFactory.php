<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentForm;
use App\Models\Learner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assessment>
 */
class AssessmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'learner_id' => Learner::factory(),
            'form_id' => AssessmentForm::factory(),
            'assessor_id' => User::factory(),
            'assessment_date' => $this->faker->date(),
            'raw_scores' => [
                'metric' => $this->faker->numberBetween(0, 100),
            ],
            'overall_result' => $this->faker->sentence(),
            'next_step' => $this->faker->sentence(),
        ];
    }
}
