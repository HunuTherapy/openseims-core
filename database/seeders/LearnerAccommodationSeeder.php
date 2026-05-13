<?php

namespace Database\Seeders;

use App\Models\AccommodationType;
use App\Models\Assessment;
use App\Models\Learner;
use App\Models\LearnerAccommodation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class LearnerAccommodationSeeder extends Seeder
{
    public function run(): void
    {
        $learners = Learner::inRandomOrder()->take(5)->get();
        $accommodationTypes = AccommodationType::inRandomOrder()->take(3)->get();
        $assessments = Assessment::inRandomOrder()->take(3)->get();

        $statuses = ['requested', 'approved', 'expired', 'canceled'];

        foreach ($learners as $learner) {
            foreach ($accommodationTypes as $type) {
                LearnerAccommodation::create([
                    'learner_id' => $learner->id,
                    'accommodation_type_id' => $type->id,
                    'status' => Arr::random($statuses),
                    'start_date' => Carbon::now()->subDays(rand(10, 100)),
                    'end_date' => rand(0, 1) ? Carbon::now()->addDays(rand(10, 100)) : null,
                    'assessment_id' => $assessments->random()->id ?? null,
                    'notes' => 'Sample note for accommodation assignment.',
                ]);
            }
        }
    }
}
