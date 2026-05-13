<?php

namespace Database\Seeders;

use App\Enums\DisabilityOnset;
use App\Models\Condition;
use App\Models\Learner;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LearnerConditionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $conditions = Condition::all();
        $learners = Learner::all();

        foreach ($learners as $learner) {
            // Assign 1 to 3 random conditions
            $assigned = $conditions->random(rand(1, 3));
            $primaryConditionId = null;

            foreach ($assigned as $index => $condition) {
                $isPrimary = $index === 0;

                DB::table('learner_condition')->updateOrInsert(
                    [
                        'learner_id' => $learner->id,
                        'condition_id' => $condition->id,
                    ],
                    [
                        'assessment_id' => null,
                        'disability_onset' => $faker->randomElement(DisabilityOnset::cases())->value,
                        'is_primary' => $isPrimary,
                        'assigned_at' => $faker->dateTimeBetween($learner->enrol_date, 'now'),
                        'notes' => $faker->optional()->sentence(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
