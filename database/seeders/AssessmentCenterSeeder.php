<?php

namespace Database\Seeders;

use App\Models\AssessmentCenter;
use Illuminate\Database\Seeder;

class AssessmentCenterSeeder extends Seeder
{
    public function run(): void
    {
        $centers = [
            ['code' => 'AC-001', 'name' => 'Central Assessment Center', 'address' => 'Accra', 'phone' => '+233200000001', 'email' => 'central@example.com'],
            ['code' => 'AC-002', 'name' => 'Northern Assessment Center', 'address' => 'Tamale', 'phone' => '+233200000002', 'email' => 'northern@example.com'],
            ['code' => 'AC-003', 'name' => 'Coastal Assessment Center', 'address' => 'Cape Coast', 'phone' => '+233200000003', 'email' => 'coastal@example.com'],
        ];

        foreach ($centers as $center) {
            AssessmentCenter::query()->updateOrCreate(
                ['code' => $center['code']],
                $center,
            );
        }
    }
}
