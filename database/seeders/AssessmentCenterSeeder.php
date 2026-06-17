<?php

namespace Database\Seeders;

use App\Models\AssessmentCenter;
use Illuminate\Database\Seeder;

class AssessmentCenterSeeder extends Seeder
{
    public function run(): void
    {
        $centers = [
            ['code' => 'AC-001', 'name' => 'Central Assessment Center', 'address' => 'District 01-01', 'phone' => '+10000000001', 'email' => 'central@example.com'],
            ['code' => 'AC-002', 'name' => 'Regional Assessment Center', 'address' => 'District 02-01', 'phone' => '+10000000002', 'email' => 'regional@example.com'],
            ['code' => 'AC-003', 'name' => 'Community Assessment Center', 'address' => 'District 03-01', 'phone' => '+10000000003', 'email' => 'community@example.com'],
        ];

        foreach ($centers as $center) {
            AssessmentCenter::query()->updateOrCreate(
                ['code' => $center['code']],
                $center,
            );
        }
    }
}
