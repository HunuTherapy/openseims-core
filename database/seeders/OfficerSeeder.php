<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OfficerSeeder extends Seeder
{
    protected array $firstNames = [
        'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda',
        'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica',
        'Thomas', 'Sarah', 'Charles', 'Karen', 'Daniel', 'Nancy', 'Matthew', 'Lisa',
        'Anthony', 'Betty', 'Mark', 'Sandra', 'Donald', 'Ashley', 'Steven', 'Kimberly',
        'Paul', 'Emily', 'Andrew', 'Donna', 'Joshua', 'Michelle', 'Kenneth', 'Dorothy',
    ];

    protected array $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
        'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
        'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
        'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker',
    ];

    public function run(): void
    {
        $districts = District::query()
            ->with('region')
            ->inRandomOrder()
            ->limit(16)
            ->get();

        $usedNames = [];

        foreach ($districts as $index => $district) {
            $name = $this->generateOfficerName($usedNames);
            $email = $this->makeOfficerEmail($name, $index + 101);

            $officerData = [
                'name' => $name,
                'role' => 'SpED Coordinator',
                'formal_training' => false,
                'phone' => $this->generatePhoneNumberPrefix(),
                'email' => $email,
                'is_deployed' => true,
            ];

            $user = User::query()->firstOrCreate(
                ['email' => $officerData['email']],
                [
                    'name' => $officerData['name'],
                    'password' => Hash::make(config('seims.imports.default_password', 'Pass1234')),
                    'region_id' => $district->region_id,
                    'district_id' => $district->id,
                ]
            );

            if (! $user->hasRole('district_officer')) {
                $user->assignRole('district_officer');
            }

            Officer::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $officerData['name'],
                    'role' => $officerData['role'],
                    'formal_training' => $officerData['formal_training'],
                    'phone' => $officerData['phone'],
                    'is_deployed' => $officerData['is_deployed'],
                ]
            );
        }
    }

    protected function generatePhoneNumberPrefix(): string
    {
        $prefix = Arr::random(['201', '202', '203', '204', '205', '206', '207', '208', '209']);

        return $prefix.(string) random_int(1000000, 9999999);
    }

    protected function generateOfficerName(array &$usedNames): string
    {
        do {
            $name = Arr::random($this->firstNames).' '.Arr::random($this->lastNames);
        } while (in_array($name, $usedNames, true));

        $usedNames[] = $name;

        return $name;
    }

    protected function makeOfficerEmail(string $name, int $suffix): string
    {
        $localPart = (string) Str::of($name)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.');

        return "{$localPart}.{$suffix}@example.com";
    }
}
