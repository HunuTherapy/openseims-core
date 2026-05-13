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
            'Kojo', 'Adjoa', 'Afriyie', 'Yaw', 'Serwaa', 'Baffour',
            'Komla', 'Edem', 'Akofa', 'Sena',
            'Ashong', 'Adjorkor', 'Tettey', 'Naa',
            'Habiba', 'Issifu', 'Sulemana', 'Shakur', 'Zuleika',
            'Emmanuel', 'Dorcas', 'Nathaniel', 'Cynthia', 'Gideon', 'Matilda',
        ];

        $lastNames = [
            'Kusi', 'Darko', 'Twum', 'Ofori', 'Bonsu', 'Agyemang',
            'Kpeglo', 'Soglo', 'Ahiable', 'Agbodza',
            'Quartey', 'Adjaye', 'Addy',
            'Issahaku', 'Bukari', 'Fuseini', 'Abudu', 'Hamidu',
            'Yeboah', 'Annan', 'Kwakye', 'Owuraku',
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
