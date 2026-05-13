<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Region;
use App\Support\GeographyData;
use Illuminate\Database\Seeder;

class RegionDistrictSeeder extends Seeder
{
    public function run(): void
    {
        foreach (GeographyData::seedPayload() as $regionPayload) {
            $region = Region::query()->firstOrCreate([
                'name' => $regionPayload['name'],
            ]);

            foreach ($regionPayload['districts'] as $districtName) {
                District::query()->firstOrCreate([
                    'region_id' => $region->id,
                    'name' => $districtName,
                ]);
            }
        }
    }
}
