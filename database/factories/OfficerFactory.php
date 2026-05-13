<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Officer>
 */
class OfficerFactory extends Factory
{
    public function definition(): array
    {
        $district = District::query()->inRandomOrder()->first();
        $user = User::factory()->create([
            'region_id' => $district?->region_id,
            'district_id' => $district?->id,
        ]);

        return [
            'name' => $this->faker->name(),
            'role' => 'District Officer',
            'formal_training' => true,
            'phone' => $this->faker->unique()->phoneNumber(),
            'is_deployed' => true,
            'user_id' => $user->id,
        ];
    }
}
