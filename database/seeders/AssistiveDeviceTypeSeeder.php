<?php

namespace Database\Seeders;

use App\Models\DeviceType;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AssistiveDeviceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Map of service type codes to device codes
        $deviceMap = [
            'SPEECH' => ['PECS_BOARD', 'COMM_BOARD', 'SPEECH_DEVICE'],
            'OCCUPATIONAL' => ['WEIGHTED_VEST', 'FIDGET_TOOL', 'SENSORY_CUSHION', 'VISUAL_TIMER'],
            'PHYSICAL' => [
                'MANUAL_WHEELCHAIR', 'POWER_WHEELCHAIR', 'WALKER', 'CRUTCHES',
                'GAIT_TRAINER', 'STANDING_FRAME', 'ADAPTIVE_SEATING', 'TRANSFER_BOARD', 'TOILET_RISER',
            ],
            'COUNSELING' => ['NOISE_EARMUFFS', 'VISUAL_TIMER', 'WEIGHTED_VEST'],
            'OTHER' => [],
        ];

        // Full list of all devices
        $devices = [
            ['code' => 'WHITE_CANE', 'name' => 'White cane', 'description' => 'Mobility aid for learners with visual impairment'],
            ['code' => 'BRAILLE_SLATE', 'name' => 'Braille slate & stylus', 'description' => 'Manual Braille writing tool'],
            ['code' => 'PERKINS_BRAILLER', 'name' => 'Perkins Brailler', 'description' => 'Mechanical Braille typewriter'],
            ['code' => 'BRAILLE_BOOKS', 'name' => 'Braille books', 'description' => 'Textbooks and readers in Braille'],
            ['code' => 'TACTILE_GLOBE', 'name' => 'Tactile globe or map', 'description' => 'Raised relief maps or globes'],
            ['code' => 'MAGNIFIER', 'name' => 'Magnifier', 'description' => 'Handheld or desktop magnifying tool'],
            ['code' => 'CCTV_MAGNIFIER', 'name' => 'CCTV video magnifier', 'description' => 'Electronic magnifier with screen'],

            ['code' => 'HEARING_AID', 'name' => 'Hearing aid', 'description' => 'Personal amplification device'],
            ['code' => 'FM_SYSTEM', 'name' => 'FM system', 'description' => 'Teacher mic + student receiver'],
            ['code' => 'SOUNDFIELD_SYSTEM', 'name' => 'Soundfield system', 'description' => 'Classroom voice amplification system'],
            ['code' => 'VISUAL_ALERT', 'name' => 'Visual alert device', 'description' => 'Vibrating or flashing alarm device'],

            ['code' => 'MANUAL_WHEELCHAIR', 'name' => 'Manual wheelchair', 'description' => 'Self- or caregiver-propelled chair'],
            ['code' => 'POWER_WHEELCHAIR', 'name' => 'Motorized wheelchair', 'description' => 'Battery-powered mobility aid'],
            ['code' => 'WALKER', 'name' => 'Walker', 'description' => 'Frame for walking stability'],
            ['code' => 'CRUTCHES', 'name' => 'Crutches', 'description' => 'Forearm or underarm walking aids'],
            ['code' => 'GAIT_TRAINER', 'name' => 'Gait trainer', 'description' => 'Supportive frame for learning to walk'],
            ['code' => 'STANDING_FRAME', 'name' => 'Standing frame', 'description' => 'Postural support to stand upright'],
            ['code' => 'ADAPTIVE_SEATING', 'name' => 'Adaptive seating', 'description' => 'Custom-fitted seat for postural needs'],
            ['code' => 'TRANSFER_BOARD', 'name' => 'Transfer board', 'description' => 'Aid to shift between wheelchair and bed/chair'],
            ['code' => 'TOILET_RISER', 'name' => 'Toilet seat riser', 'description' => 'Raised seat for accessible toileting'],

            ['code' => 'PECS_BOARD', 'name' => 'PECS board/book', 'description' => 'Picture-based communication tool'],
            ['code' => 'COMM_BOARD', 'name' => 'Communication board', 'description' => 'Letter/symbol communication board'],
            ['code' => 'SPEECH_DEVICE', 'name' => 'Speech-generating device', 'description' => 'Dedicated hardware AAC tool'],
            ['code' => 'EYE_GAZE_BOARD', 'name' => 'Eye gaze board', 'description' => 'Transparent board for eye-pointing'],

            ['code' => 'WEIGHTED_VEST', 'name' => 'Weighted vest', 'description' => 'Vest to provide calming deep pressure'],
            ['code' => 'FIDGET_TOOL', 'name' => 'Fidget tool', 'description' => 'Handheld manipulative for focus'],
            ['code' => 'SENSORY_CUSHION', 'name' => 'Sensory cushion', 'description' => 'Wiggle seat or tactile cushion'],
            ['code' => 'NOISE_EARMUFFS', 'name' => 'Noise-canceling earmuffs', 'description' => 'Blocks noise for hypersensitive learners'],
            ['code' => 'VISUAL_TIMER', 'name' => 'Visual timer', 'description' => 'Timer with visual countdown'],
        ];

        $allDeviceCodes = collect($devices)->pluck('code')->toArray();
        $usedCodes = collect($deviceMap)->flatten()->unique()->toArray();
        $otherCodes = array_diff($allDeviceCodes, $usedCodes);

        // Add unmatched codes to 'OTHER'
        $deviceMap['OTHER'] = array_merge($deviceMap['OTHER'], $otherCodes);

        foreach ($devices as $device) {
            $deviceModel = DeviceType::updateOrCreate(
                ['code' => $device['code']],
                [
                    'name' => $device['name'],
                    'description' => $device['description'],
                ]
            );

            // Attach relationships
            foreach ($deviceMap as $serviceCode => $deviceCodes) {
                if (in_array($device['code'], $deviceCodes)) {
                    $serviceType = ServiceType::where('code', $serviceCode)->first();
                    if ($serviceType) {
                        DB::table('device_type_service_type')->updateOrInsert(
                            [
                                'device_type_id' => $deviceModel->id,
                                'service_type_id' => $serviceType->id,
                            ],
                            [
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]
                        );
                    }
                }
            }
        }
    }
}
