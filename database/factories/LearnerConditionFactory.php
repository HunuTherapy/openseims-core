<?php

namespace Database\Factories;

use App\Models\Condition;
use App\Models\Learner;
use App\Models\LearnerCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerCondition>
 */
class LearnerConditionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'learner_id' => Learner::factory(),
            'condition_id' => Condition::factory(),
            'assessment_id' => null,
            'is_primary' => false,
            'status' => 'provisional',
            'disability_onset' => 'congenital',
            'assigned_at' => $this->faker->date(),
            'notes' => $this->faker->sentence(),
        ];
    }
}
