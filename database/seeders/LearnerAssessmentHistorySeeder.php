<?php

namespace Database\Seeders;

use App\Models\AssessmentCenter;
use App\Models\Learner;
use App\Models\LearnerAssessmentHistory;
use Illuminate\Database\Seeder;

class LearnerAssessmentHistorySeeder extends Seeder
{
    public function run(): void
    {
        $learners = Learner::query()->limit(50)->get();
        $centers = AssessmentCenter::query()->get();

        if ($learners->isEmpty() || $centers->isEmpty()) {
            return;
        }

        foreach ($learners as $learner) {
            // Not every learner has an assessment history entry.
            if (random_int(1, 100) > 45) {
                continue;
            }

            $center = $centers->random();

            LearnerAssessmentHistory::factory()->create([
                'learner_id' => $learner->id,
                'centerable_type' => $center::class,
                'centerable_id' => $center->id,
                'referred_to_center_id' => $center->id,
                'event_type' => 'assessment',
            ]);
        }
    }
}
