<?php

namespace Database\Factories;

use App\Models\AssessmentCenter;
use App\Models\Learner;
use App\Models\LearnerAssessmentHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerAssessmentHistory>
 */
class LearnerAssessmentHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'learner_id' => Learner::factory(),
            'centerable_type' => AssessmentCenter::class,
            'centerable_id' => AssessmentCenter::factory(),
            'event_type' => $this->faker->randomElement(['screening', 'assessment']),
            'referred_to_center_id' => AssessmentCenter::factory(),
            'event_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'notes' => $this->faker->sentence(),
        ];
    }
}
