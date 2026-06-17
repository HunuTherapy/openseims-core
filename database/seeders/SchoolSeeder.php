<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = collect(range(1, 16))
            ->map(fn (int $regionNumber): array => [
                'emis_code' => sprintf('100%05d', $regionNumber),
                'name' => sprintf('Example School %02d', $regionNumber),
                'district_name' => sprintf('District %02d-01', $regionNumber),
                'region_name' => sprintf('Region %02d', $regionNumber),
                'school_type' => $regionNumber % 5 === 0 ? 'special_unit' : ($regionNumber % 3 === 0 ? 'private' : 'public'),
                'is_inclusive' => $regionNumber % 3 !== 0,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => $regionNumber % 3 === 0 ? 'no' : 'yes'],
                    ['feature' => 'braille_signage', 'value' => $regionNumber % 4 === 0 ? 'yes' : 'no'],
                    ['feature' => 'wheelchair', 'value' => $regionNumber % 2 === 0 ? 'yes' : 'no'],
                ],
            ])
            ->all();

        collect($schools)
            ->map(function (array $school): array {
                $districtId = $this->resolveDistrictId($school['region_name'], $school['district_name']);

                unset($school['region_name'], $school['district_name']);

                return [
                    ...$school,
                    'district_id' => $districtId,
                ];
            })
            ->each(function (array $school): void {
                School::query()->updateOrCreate(
                    ['emis_code' => $school['emis_code']],
                    $school,
                );
            });
    }

    private function resolveDistrictId(string $regionName, string $districtName): int
    {
        return District::query()
            ->where('name', $districtName)
            ->whereHas('region', fn ($query) => $query->where('name', $regionName))
            ->valueOrFail('id');
    }
}
