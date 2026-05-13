<?php

namespace Database\Seeders;

use App\Enums\EvaluationDecision;
use App\Enums\FrequencyUnit;
use App\Enums\GoalCompletionStatus;
use App\Enums\GoalType;
use App\Enums\InstructionArea;
use App\Enums\ParentalConsent;
use App\Models\Learner;
use App\Models\ServiceType;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IepGoalSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $learners = Learner::inRandomOrder()->take(50)->get();
        $serviceCodes = ServiceType::pluck('code')->toArray();
        $userIds = User::pluck('id')->toArray();

        foreach ($learners as $learner) {
            $placement = Arr::random([
                'self_contained_full_time',
                'self_contained_part_time',
                'is_inclusive',
                'regular_class_with_support',
                'other',
            ]);

            $relatedServices = $faker->randomElements($serviceCodes, rand(1, count($serviceCodes)));

            $goalId = DB::table('iep_goals')->insertGetId([
                'learner_id' => $learner->id,
                'frequency_value' => $faker->numberBetween(1, 5),
                'frequency_unit' => Arr::random(FrequencyUnit::cases())->value,
                'start_date' => Carbon::now()->subMonths(rand(1, 6)),
                'end_date' => Carbon::now()->addMonths(rand(2, 6)),
                'parental_consent' => Arr::random(ParentalConsent::cases())->value,
                'program_placement' => $placement,
                'program_placement_other' => $placement === 'other' ? $faker->sentence(3) : null,
                'related_services' => json_encode($relatedServices),
                'related_services_other' => in_array('OTHER', $relatedServices, true) ? $faker->sentence(3) : null,
                'evaluation_decision' => Arr::random(EvaluationDecision::cases())->value,
                'status' => Arr::random(['on_track', 'lagging', 'achieved']),
                'goal_type' => Arr::random(GoalType::cases())->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            for ($i = 0, $n = rand(1, 3); $i < $n; $i++) {
                DB::table('iep_goal_entries')->insert([
                    'iep_goal_id' => $goalId,
                    'instruction_area' => Arr::random(InstructionArea::cases())->value,
                    'baseline' => $faker->numberBetween(0, 100),
                    'baseline_description' => $faker->optional()->sentence(),
                    'target' => $faker->numberBetween(0, 100),
                    'target_description' => $faker->optional()->sentence(),
                    'last_review_at' => $faker->optional()->dateTimeBetween('-3 months', 'now'),
                    'completion_status' => Arr::random(GoalCompletionStatus::cases())->value,
                    'recommend_goal_change' => $faker->boolean(20),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $members = $faker->randomElements($userIds, rand(1, min(3, count($userIds))));

            foreach ($members as $userId) {
                $role = Arr::random([
                    'teacher',
                    'school_coordinator',
                    'therapist',
                    'counselor',
                    'parent',
                    'administrator',
                    'sped_coordinator',
                    'other',
                ]);

                DB::table('iep_team_members')->insert([
                    'iep_goal_id' => $goalId,
                    'user_id' => $userId,
                    'is_guest' => false,
                    'guest_name' => null,
                    'role' => $role,
                    'custom_role' => $role === 'other' ? $faker->jobTitle() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
