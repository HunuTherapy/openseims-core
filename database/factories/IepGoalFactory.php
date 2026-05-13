<?php

namespace Database\Factories;

use App\Enums\GoalType;
use App\Models\IepGoal;
use App\Models\Learner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IepGoal>
 */
class IepGoalFactory extends Factory
{
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate = $this->faker->dateTimeBetween('now', '+1 year');

        return [
            'learner_id' => Learner::factory(),
            'frequency_value' => $this->faker->numberBetween(1, 5),
            'frequency_unit' => 'week',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'parental_consent' => 'participated_and_approve',
            'program_placement' => 'is_inclusive',
            'related_services' => [],
            'status' => 'on_track',
            'goal_type' => GoalType::SHORT_TERM->value,
        ];
    }
}
