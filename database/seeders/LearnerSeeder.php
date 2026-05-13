<?php

namespace Database\Seeders;

use App\Enums\LearnerClass;
use App\Models\Learner;
use App\Models\School;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class LearnerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $firstNames = [
            'Kwame', 'Ama', 'Yaw', 'Abena', 'Selorm', 'Delali', 'Mawuli', 'Eyram', 'Nii', 'Naa', 'Tetteh', 'Mohammed',
            'Aisha', 'Sadat', 'Issahaku', 'Alhassan', 'Rita', 'Priscilla', 'Samuel', 'Joseph', 'Comfort', 'Naana',
        ];

        $lastNames = [
            'Mensah', 'Owusu', 'Osei', 'Addo', 'Agbeko', 'Dzakpasu', 'Dzifa', 'Lamptey', 'Lartey', 'Tetteh',
            'Yakubu', 'Ibrahim', 'Mahama', 'Alhassan', 'Asamoah', 'Tagoe', 'Boateng', 'Ampofo',
        ];

        $statuses = ['enrolled', 'transferred', 'exited'];

        $schools = School::all();

        foreach ($schools as $school) {
            for ($i = 0; $i < 10; $i++) {
                $sex = $faker->randomElement(['M', 'F']);
                $firstName = $faker->randomElement($firstNames);
                $lastName = $faker->randomElement($lastNames);

                Learner::create([
                    'first_name' => $firstName,
                    'middle_name' => $faker->optional()->firstName,
                    'last_name' => $lastName,
                    'school_id' => $school->id,
                    'sex' => $sex,
                    'date_of_birth' => $faker->dateTimeBetween('-12 years', '-5 years'),
                    'enrol_date' => $faker->dateTimeBetween('-5 years', 'now'),
                    'class' => $faker->randomElement(LearnerClass::class),    // KG-JHS3
                    'primary_language' => $faker->randomElement(['Akan', 'Ewe', 'Ga', 'English']),
                    'primary_contact_name' => $faker->name,
                    'status' => $faker->randomElement($statuses),
                    'academic_strengths' => $faker->sentence(),
                    'academic_weaknesses' => $faker->sentence(),
                    'social_life_observations' => $faker->optional()->sentence(),
                    'extracurricular_activity_notes' => $faker->optional()->sentence(),
                    'referred_at' => $faker->boolean(30) ? $faker->dateTimeBetween('-2 years', 'now') : null,
                ]);
            }
        }
    }
}
