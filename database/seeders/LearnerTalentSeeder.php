<?php

namespace Database\Seeders;

use App\Models\Learner;
use App\Models\Talent;
use Illuminate\Database\Seeder;

class LearnerTalentSeeder extends Seeder
{
    public function run(): void
    {
        $learners = Learner::all();
        $talents = Talent::all();

        foreach ($learners as $learner) {
            // Randomly decide if this learner should have talents
            if (rand(0, 1) === 1) {
                // Attach a random number of talents (1 to 3) to the learner
                $randomTalents = $talents->random(rand(1, min(3, $talents->count())))->pluck('id')->toArray();
                $learner->talents()->syncWithoutDetaching($randomTalents);
            }
        }
    }
}
