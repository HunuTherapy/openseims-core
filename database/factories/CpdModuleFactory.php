<?php

namespace Database\Factories;

use App\Models\CpdModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CpdModule>
 */
class CpdModuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->paragraph(),
        ];
    }
}
