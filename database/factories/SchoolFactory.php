<?php

namespace Database\Factories;

use App\Enums\SchoolLevel;
use App\Models\District;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    public function definition(): array
    {
        $districtId = District::query()->inRandomOrder()->value('id');

        return [
            'emis_code' => $this->faker->unique()->numerify('########'),
            'name' => $this->faker->company().' School',
            'district_id' => $districtId,
            'school_level' => $this->faker->randomElement(array_map(
                fn (SchoolLevel $level): string => $level->value,
                SchoolLevel::cases()
            )),
            'school_type' => 'public',
            'is_inclusive' => $this->faker->boolean(),
            'resource_teacher' => $this->faker->boolean(),
            'number_of_teachers' => $this->faker->numberBetween(1, 80),
            'accessibility' => null,
        ];
    }
}
