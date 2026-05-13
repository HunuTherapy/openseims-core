<?php

namespace Database\Seeders;

use App\Models\DeviceType;
use App\Models\Learner;
use Illuminate\Database\Seeder;

class LearnerDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $learners = Learner::all();
        $deviceTypes = DeviceType::all();

        foreach ($learners as $learner) {
            // Randomly decide if this learner should have devices
            if (rand(0, 1) === 1) {
                // Attach a random number of device types (1 to 3) with fulfilled_at date
                $randomDevices = $deviceTypes->random(rand(1, min(3, $deviceTypes->count())))->pluck('id')->toArray();
                $pivotData = [];
                foreach ($randomDevices as $deviceId) {
                    $pivotData[$deviceId] = [
                        'requested_at' => now()->subDays(rand(10, 100)),
                        'fulfilled_at' => now()->subDays(rand(1, 9)),
                        'returned_at' => null,
                        'serial_number' => 'SN'.rand(10000, 99999),
                    ];
                }
                $learner->deviceTypes()->attach($pivotData);
            }
        }
    }
}
