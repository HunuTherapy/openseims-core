<?php

namespace Database\Seeders;

use App\Models\IePractice;
use Illuminate\Database\Seeder;

class IePracticeSeeder extends Seeder
{
    public function run(): void
    {
        $practices = [
            ['name' => 'Universal Design for Learning', 'description' => 'Providing multiple means of representation, engagement, and expression.'],
            ['name' => 'Differentiated Instruction', 'description' => 'Tailoring instruction to meet individual needs.'],
            ['name' => 'Assistive Technology Integration', 'description' => 'Using technology to support learners with disabilities.'],
            ['name' => 'Collaborative Teaching', 'description' => 'Co-teaching strategies for inclusive classrooms.'],
            ['name' => 'Positive Behavioral Interventions', 'description' => 'Strategies to support positive behavior.'],
        ];

        foreach ($practices as $practice) {
            IePractice::create($practice);
        }
    }
}
