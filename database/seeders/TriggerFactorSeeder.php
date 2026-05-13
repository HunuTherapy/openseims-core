<?php

namespace Database\Seeders;

use App\Models\TriggerFactor;
use Illuminate\Database\Seeder;

class TriggerFactorSeeder extends Seeder
{
    public function run(): void
    {
        $factors = [
            ['code' => 'NOISE', 'name' => 'Noise', 'description' => 'Loud environments that may cause discomfort or distraction.'],
            ['code' => 'LIGHT', 'name' => 'Light', 'description' => 'Bright or flashing lights that may overwhelm the learner.'],
            ['code' => 'SOCIAL', 'name' => 'Social Interaction', 'description' => 'Interactions with peers or groups that may be stressful.'],
        ];

        foreach ($factors as $factor) {
            TriggerFactor::query()->updateOrCreate(
                ['code' => $factor['code']],
                $factor,
            );
        }
    }
}
