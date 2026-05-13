<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RegionDistrictSeeder::class,
            ConditionCategorySeeder::class,
            ConditionSeeder::class,
            SchoolAccessibilityFeatureSeeder::class,
            SchoolSeeder::class,
            TeacherSeeder::class,
            LearnerSeeder::class,
            LearnerConditionSeeder::class,
            TriggerFactorSeeder::class,
            TalentSeeder::class,
            ServiceTypeSeeder::class,
            AssistiveDeviceTypeSeeder::class,
            AssessmentFormSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            OfficerSeeder::class,
            AccommodationTypeSeeder::class,
            LearnerTalentSeeder::class,
            IepGoalSeeder::class,
            IePracticeSeeder::class,
            CpdModuleSeeder::class,
            AssessmentCenterSeeder::class,
            LearnerDeviceSeeder::class,
            LearnerAssessmentHistorySeeder::class,
        ]);
    }
}
