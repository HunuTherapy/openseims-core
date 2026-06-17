<?php

namespace Database\Seeders;

use App\Enums\LearnerClass;
use App\Enums\TeacherType;
use App\Models\School;
use App\Models\Teacher;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $firstNames = [
            'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer',
            'Michael', 'Linda', 'William', 'Elizabeth', 'David', 'Barbara',
            'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah',
            'Charles', 'Karen', 'Daniel', 'Nancy', 'Matthew', 'Lisa',
        ];

        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia',
            'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez',
            'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore',
            'Jackson', 'Martin', 'Lee', 'Perez',
        ];

        $schools = School::all();

        foreach ($schools as $schoolIndex => $school) {
            for ($i = 0; $i < 5; $i++) {
                $firstName = $faker->boolean(60)
                    ? $faker->randomElement($firstNames)
                    : $faker->firstName;

                $lastName = $faker->boolean(70)
                    ? $faker->randomElement($lastNames)
                    : $faker->lastName;

                $teacherNo = 'TCH'.str_pad(($schoolIndex * 10) + $i, 4, '0', STR_PAD_LEFT);

                Teacher::query()->updateOrCreate([
                    'teacher_no' => $teacherNo,
                ], [
                    'teacher_type' => TeacherType::CLASS_TEACHER->value,
                    'school_id' => $school->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'class' => $faker->randomElement(array_map(
                        fn (LearnerClass $class) => $class->value,
                        LearnerClass::cases()
                    )),
                    'qualification' => $faker->randomElement(['Diploma', 'B.Ed', 'M.Ed']),
                    'training_on_inclusion' => $faker->boolean(50),
                    'skills' => $faker->boolean(50) ? $faker->words(3, true) : null,
                    'other_qualifications' => $faker->boolean(40) ? $faker->sentence() : null,
                    'in_service_trainings_attended' => $faker->boolean(60) ? $faker->numberBetween(0, 15) : null,
                    'sen_certified' => $faker->boolean(40),
                    'is_deployed' => $faker->boolean(70), // 70% chance deployed
                ]);
            }
        }
    }
}
