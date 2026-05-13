<?php

namespace Database\Seeders;

use App\Models\CpdModule;
use App\Models\IePractice;
use Illuminate\Database\Seeder;

class CpdModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Get IE practices to attach
        $iePractices = IePractice::all();

        // Create CPD modules, some with IE practices, some without
        $modulesData = [
            ['name' => 'Introduction to Special Education', 'ie_practices' => []],
            ['name' => 'Inclusive Classroom Strategies', 'ie_practices' => $iePractices->take(2)->pluck('id')->toArray()],
            ['name' => 'Assistive Technology Basics', 'ie_practices' => $iePractices->where('name', 'Assistive Technology Integration')->pluck('id')->toArray()],
            ['name' => 'Behavioral Interventions', 'ie_practices' => $iePractices->where('name', 'Positive Behavioral Interventions')->pluck('id')->toArray()],
            ['name' => 'Advanced Differentiated Instruction', 'ie_practices' => $iePractices->where('name', 'Differentiated Instruction')->pluck('id')->toArray()],
            ['name' => 'General Teaching Methods', 'ie_practices' => []],
        ];

        foreach ($modulesData as $data) {
            $module = CpdModule::create(['name' => $data['name']]);
            if (! empty($data['ie_practices'])) {
                $module->iePractices()->attach($data['ie_practices']);
            }
        }
    }
}
