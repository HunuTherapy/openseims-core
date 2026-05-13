<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            [
                'emis_code' => 'GH110001',
                'name' => 'Abeka Basic School',
                'district_name' => 'Ablekuma North',
                'region_name' => 'Greater Accra',
                'school_type' => 'public',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'braille_signage', 'value' => 'no'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],
            [
                'emis_code' => 'GH208012',
                'name' => 'St. Theresa Special Unit',
                'district_name' => 'Sunyani',
                'region_name' => 'Bono',
                'school_type' => 'special_unit',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'braille_signage', 'value' => 'yes'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                    ['feature' => 'sign_language_support', 'value' => 'yes'],
                ],
            ],
            [
                'emis_code' => 'GH309074',
                'name' => 'Tamale Stars Academy',
                'district_name' => 'Tamale',
                'region_name' => 'Northern',
                'school_type' => 'private',
                'is_inclusive' => false,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'no'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],
            // Ashanti
            [
                'emis_code' => 'GH406023',
                'name' => 'Kumasi Adventist Basic',
                'district_name' => 'Kumasi',
                'region_name' => 'Ashanti',
                'school_type' => 'public',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'braille_signage', 'value' => 'yes'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],
            [
                'emis_code' => 'GH406099',
                'name' => 'Garden City Deaf School',
                'district_name' => 'Kumasi',
                'region_name' => 'Ashanti',
                'school_type' => 'special_unit',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'sign_language_support', 'value' => 'yes'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],

            // Eastern
            [
                'emis_code' => 'GH507045',
                'name' => 'Koforidua Model Junior High',
                'district_name' => 'New Juaben South',
                'region_name' => 'Eastern',
                'school_type' => 'public',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],

            // Upper East
            [
                'emis_code' => 'GH608010',
                'name' => 'Bolgatanga Inclusive Primary',
                'district_name' => 'Bolgatanga',
                'region_name' => 'Upper East',
                'school_type' => 'public',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'braille_signage', 'value' => 'no'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],

            // Western
            [
                'emis_code' => 'GH709032',
                'name' => 'Takoradi Sunshine School',
                'district_name' => 'Sekondi Takoradi',
                'region_name' => 'Western',
                'school_type' => 'private',
                'is_inclusive' => false,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'no'],
                ],
            ],

            // Volta
            [
                'emis_code' => 'GH810024',
                'name' => 'Ho Hope Special Needs Centre',
                'district_name' => 'Ho',
                'region_name' => 'Volta',
                'school_type' => 'special_unit',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'braille_signage', 'value' => 'yes'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],

            // Central
            [
                'emis_code' => 'GH911050',
                'name' => 'Cape Coast Inclusive JHS',
                'district_name' => 'Cape Coast',
                'region_name' => 'Central',
                'school_type' => 'public',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'braille_signage', 'value' => 'yes'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],

            // Oti
            [
                'emis_code' => 'GH1011021',
                'name' => 'Dambai Community School',
                'district_name' => 'Krachi East',
                'region_name' => 'Oti',
                'school_type' => 'public',
                'is_inclusive' => false,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'no'],
                    ['feature' => 'wheelchair', 'value' => 'no'],
                ],
            ],

            // Savannah
            [
                'emis_code' => 'GH1113055',
                'name' => 'Damongo Girls Basic',
                'district_name' => 'West Gonja',
                'region_name' => 'Savannah',
                'school_type' => 'public',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                ],
            ],

            // North East
            [
                'emis_code' => 'GH1212012',
                'name' => 'Nalerigu Inclusive Primary',
                'district_name' => 'East Mamprusi',
                'region_name' => 'North East',
                'school_type' => 'public',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],

            // Upper West
            [
                'emis_code' => 'GH1314099',
                'name' => 'Wa Hope School',
                'district_name' => 'Wa',
                'region_name' => 'Upper West',
                'school_type' => 'private',
                'is_inclusive' => false,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'no'],
                ],
            ],
            // Upper West – inclusive kindergarten
            [
                'emis_code' => 'GH1314100',
                'name' => 'Wa Inclusive Kindergarten',
                'district_name' => 'Wa',
                'region_name' => 'Upper West',
                'school_type' => 'public',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'braille_signage', 'value' => 'yes'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],

            // Western North
            [
                'emis_code' => 'GH1412044',
                'name' => 'Bibiani Bright Future Academy',
                'district_name' => 'Bibiani Anhwiaso Bekwai',
                'region_name' => 'Western North',
                'school_type' => 'private',
                'is_inclusive' => false,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'no'],
                    ['feature' => 'wheelchair', 'value' => 'no'],
                ],
            ],

            // Greater Accra – special unit
            [
                'emis_code' => 'GH110189',
                'name' => 'Accra Special School',
                'district_name' => 'Accra',
                'region_name' => 'Greater Accra',
                'school_type' => 'special_unit',
                'is_inclusive' => true,
                'accessibility' => [
                    ['feature' => 'ramp', 'value' => 'yes'],
                    ['feature' => 'sign_language_support', 'value' => 'yes'],
                    ['feature' => 'braille_signage', 'value' => 'yes'],
                    ['feature' => 'wheelchair', 'value' => 'yes'],
                ],
            ],
        ];

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
