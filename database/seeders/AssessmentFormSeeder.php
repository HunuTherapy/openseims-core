<?php

namespace Database\Seeders;

use App\Models\AssessmentForm;
use Illuminate\Database\Seeder;

class AssessmentFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // use faker to create 500 records with name, description, url, active

        $records = [];

        $forms = [
            'MCHAT-R/F (Modified Checklist for Autism in Toddlers)',
            'Vineland Adaptive Behavior Scales, Third Edition (Vineland-3)',
            'Wechsler Intelligence Scale for Children, Fifth Edition (WISC-V)',
            'Peabody Developmental Motor Scales, Third Edition (PDMS-3)',
            'Bruininks-Oseretsky Test of Motor Proficiency, Second Edition (BOT-2)',
            'Gross Motor Function Measure (GMFM-66)',
            'Autism Diagnostic Observation Schedule, Second Edition (ADOS-2)',
            'Adaptive Behavior Assessment System, Third Edition (ABAS-3)',
            'Developmental Assessment for Individuals with Severe Disabilities, Third Edition (DASH-3)',

        ];

        foreach ($forms as $form) {
            $records[] = [
                'name' => $form,
                'description' => fake()->paragraph(),
                'url' => fake()->unique()->url(),
                'active' => fake()->boolean(),
                'responses_count' => fake()->randomNumber(4),
            ];
        }

        if (AssessmentForm::query()->count() === 0) {
            AssessmentForm::query()->insert($records);
        }
    }
}
