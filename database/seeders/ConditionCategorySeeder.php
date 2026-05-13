<?php

namespace Database\Seeders;

use App\Models\ConditionCategory;
use Illuminate\Database\Seeder;

class ConditionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['code' => 'DISABILITY', 'name' => 'Disability'],
            ['code' => 'PERSONAL_FACTOR', 'name' => 'Personal Factor'],
        ] as $category) {
            ConditionCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                ['name' => $category['name']],
            );
        }
    }
}
