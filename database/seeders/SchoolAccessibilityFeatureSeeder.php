<?php

namespace Database\Seeders;

use App\Models\SchoolAccessibilityFeature;
use Illuminate\Database\Seeder;

class SchoolAccessibilityFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'code' => 'ramp',
                'name' => 'Ramp',
                'description' => 'Ramps that allow step-free access to key areas.',
            ],
            [
                'code' => 'wheelchair',
                'name' => 'Wheelchair Access',
                'description' => 'Spaces and pathways that are wheelchair accessible.',
            ],
            [
                'code' => 'braille_signage',
                'name' => 'Braille Signage',
                'description' => 'Braille signage for navigation and wayfinding.',
            ],
            [
                'code' => 'sign_language_support',
                'name' => 'Sign Language Support',
                'description' => 'Access to sign language support or interpretation.',
            ],
        ];

        foreach ($features as $feature) {
            SchoolAccessibilityFeature::updateOrCreate(
                ['code' => $feature['code']],
                $feature,
            );
        }
    }
}
