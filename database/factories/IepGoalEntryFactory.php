<?php

namespace Database\Factories;

use App\Enums\GoalCompletionStatus;
use App\Models\IepGoal;
use App\Models\IepGoalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IepGoalEntry>
 */
class IepGoalEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'iep_goal_id' => IepGoal::factory(),
            'baseline' => 10,
            'instruction_area' => 'literacy',
            'target' => 50,
            'completion_status' => GoalCompletionStatus::NOT_STARTED->value,
            'recommend_goal_change' => false,
        ];
    }
}
