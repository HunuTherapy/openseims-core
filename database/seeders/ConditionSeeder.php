<?php

namespace Database\Seeders;

use App\Models\Condition;
use App\Models\ConditionCategory;
use Illuminate\Database\Seeder;

class ConditionSeeder extends Seeder
{
    public function run(): void
    {

        $disabilityId = ConditionCategory::where('code', 'DISABILITY')->value('id');
        $personalFactorId = ConditionCategory::where('code', 'PERSONAL_FACTOR')->value('id');

        $disabilities = [
            [
                'code' => 'DEAF',
                'name' => 'Deaf / Deafness',
                'description' => 'A condition where an individual has a significant hearing loss.',
            ],
            [
                'code' => 'NON_VERBAL',
                'name' => 'Non-verbal communication (non-speaking)',
                'description' => 'A condition where an individual is unable to speak.',
            ],
            [
                'code' => 'PHYSICAL_DISABILITY',
                'name' => 'Physical disability',
                'description' => 'A condition affecting physical movement or dexterity.',
            ],
            [
                'code' => 'VISUAL_DISABILITY',
                'name' => 'Visual disability',
                'description' => 'A condition affecting vision or sight.',
            ],
            [
                'code' => 'INTELLECTUAL_DISABILITY',
                'name' => 'Intellectual disability',
                'description' => 'A condition affecting intellectual functioning and adaptive behavior.',
            ],
            [
                'code' => 'AUTISM',
                'name' => 'Autism',
                'description' => 'A developmental disorder affecting communication and behavior.',
            ],
            [
                'code' => 'DOWN_SYNDROME',
                'name' => 'Down syndrome',
                'description' => 'A genetic disorder causing developmental and intellectual delays.',
            ],
            [
                'code' => 'BIPOLAR_CONDITION',
                'name' => 'Bipolar condition',
                'description' => 'A mental health condition causing extreme mood swings.',
            ],
        ];

        foreach ($disabilities as $disability) {
            Condition::updateOrCreate(
                ['code' => $disability['code']],
                [
                    'name' => $disability['name'],
                    'description' => $disability['description'],
                    'category_id' => $disabilityId,
                ]
            );
        }

        $personalFactors = [
            [
                'code' => 'ORPHAN',
                'name' => 'Orphan',
                'description' => 'A child whose parents are deceased.',
            ],
            [
                'code' => 'POVERTY',
                'name' => 'Poverty / low-income household',
                'description' => 'Living in conditions of financial hardship.',
            ],
            [
                'code' => 'DISPLACED',
                'name' => 'Displacement by disaster or conflict',
                'description' => 'Forced relocation due to natural disasters or conflict.',
            ],
            [
                'code' => 'PARENT_CHRONIC_ILLNESS',
                'name' => 'Living with parent(s) with chronic or terminal illness',
                'description' => 'A child living with a parent who has a long-term illness.',
            ],
            [
                'code' => 'FOSTER_CHILD',
                'name' => 'Living with foster parents',
                'description' => 'A child living with caregivers other than their biological parents.',
            ],
            [
                'code' => 'TRAUMA',
                'name' => 'Emotional or physical trauma',
                'description' => 'A condition resulting from a distressing experience.',
            ],
        ];

        foreach ($personalFactors as $factor) {
            Condition::updateOrCreate(
                ['code' => $factor['code']],
                [
                    'name' => $factor['name'],
                    'description' => $factor['description'],
                    'category_id' => $personalFactorId,
                ]
            );
        }
    }
}
