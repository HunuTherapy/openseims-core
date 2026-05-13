<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $services = [
            [
                'code' => 'SPEECH',
                'name' => 'Speech and Language Therapy',
                'description' => 'Support for communication, language, and speech development',
            ],
            [
                'code' => 'OCCUPATIONAL',
                'name' => 'Occupational Therapy',
                'description' => 'Therapy to support motor skills, coordination, and daily living activities',
            ],
            [
                'code' => 'PHYSICAL',
                'name' => 'Physical Therapy',
                'description' => 'Intervention focused on mobility, strength, and physical development',
            ],
            [
                'code' => 'COUNSELING',
                'name' => 'Counseling',
                'description' => 'Emotional, behavioral, and psychological support services',
            ],
            [
                'code' => 'OTHER',
                'name' => 'Other',
                'description' => 'Custom or unspecified support services as identified by the IEP team',
            ],
        ];

        foreach ($services as $service) {
            DB::table('service_types')->updateOrInsert(
                ['code' => $service['code']],
                [
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'updated_at' => $now,
                    'created_at' => DB::raw('IFNULL(created_at, NOW())'),
                ]
            );
        }
    }
}
