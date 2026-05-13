<?php

namespace Database\Seeders;

use App\Enums\LearnerClass;
use App\Enums\TeacherType;
use App\Models\Officer;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $defaultPassword = '$2y$12$sypTJWu2Dv7y0D1M10n1ceXXp3NQa1MYugkppWQrDGCEc29nkab0G';

        $school = School::query()->first();

        $users = [
            [
                'name' => 'National Admin',
                'email' => 'national.admin@example.com',
                'password' => $defaultPassword,
                'role' => 'national_admin',
                'scope' => [],
                'profile' => null,
            ],
            [
                'name' => 'District Officer',
                'email' => 'district.officer@example.com',
                'password' => $defaultPassword,
                'role' => 'district_officer',
                'scope' => $school ? [
                    'region_id' => $school->district?->region_id,
                    'district_id' => $school->district_id,
                ] : [],
                'profile' => 'officer',
            ],
            [
                'name' => 'School Coordinator',
                'email' => 'school.coordinator@example.com',
                'password' => $defaultPassword,
                'role' => 'school_coordinator',
                'scope' => $school ? [
                    'region_id' => $school->district?->region_id,
                    'district_id' => $school->district_id,
                    'school_id' => $school->id,
                ] : [],
                'profile' => 'teacher',
            ],
        ];

        $nextTeacherNo = static function (): string {
            return 'TCH-'.hrtime(true);
        };

        foreach ($users as $userData) {
            $user = User::query()->firstOrCreate(
                ['email' => $userData['email']],
                array_merge([
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                ], $userData['scope'])
            );

            $user->syncRoles([$userData['role']]);

            if (! $school || $userData['profile'] === null) {
                continue;
            }

            if ($userData['profile'] === 'officer') {
                Officer::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => $user->name,
                        'role' => 'District Officer',
                        'formal_training' => true,
                        'phone' => $faker->unique()->phoneNumber(),
                        'is_deployed' => true,
                    ]
                );
            }

            if ($userData['profile'] === 'teacher') {
                Teacher::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'teacher_no' => $nextTeacherNo(),
                        'teacher_type' => TeacherType::SCHOOL_COORDINATOR->value,
                        'school_id' => $school->id,
                        'first_name' => $faker->firstName(),
                        'last_name' => $faker->lastName(),
                        'class' => $faker->randomElement(array_map(
                            fn (LearnerClass $class) => $class->value,
                            LearnerClass::cases()
                        )),
                        'qualification' => $faker->randomElement(['Diploma', 'B.Ed', 'M.Ed']),
                        'training_on_inclusion' => true,
                        'sen_certified' => true,
                        'is_deployed' => true,
                    ]
                );
            }
        }
    }
}
