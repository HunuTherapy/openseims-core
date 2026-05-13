<?php

namespace Database\Seeders;

use App\Models\AccommodationType;
use Illuminate\Database\Seeder;

class AccommodationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $accommodations = [
            [
                'code' => 'EXTRA_TIME',
                'name' => 'Extra Time',
                'description' => 'Additional time for assessments to accommodate various needs.',
            ],
            [
                'code' => 'LARGE_PRINT',
                'name' => 'Large-Print Paper',
                'description' => 'Printed materials in larger font sizes for better readability.',
            ],
            ['code' => 'READER',
                'name' => 'Human Reader',
                'description' => 'A person who reads assessment materials aloud to the learner.',
            ],
            [
                'code' => 'SCRIBE',
                'name' => 'Writer / Scribe',
                'description' => 'A person who writes down the learner\'s responses during assessments.',
            ],
            [
                'code' => 'SIGNER',
                'name' => 'Sign-Language Interpreter',
                'description' => 'An interpreter who translates spoken language into sign language for the learner.',
            ],
        ];

        foreach ($accommodations as $accommodation) {
            AccommodationType::updateOrCreate(['code' => $accommodation['code']], $accommodation);
        }
    }
}
