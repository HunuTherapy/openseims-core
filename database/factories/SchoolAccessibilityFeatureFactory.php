<?php

namespace Database\Factories;

use App\Models\SchoolAccessibilityFeature;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SchoolAccessibilityFeature>
 */
class SchoolAccessibilityFeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::of($this->faker->unique()->words(2, true))
            ->title()
            ->toString();

        return [
            'code' => Str::of($name)
                ->lower()
                ->replace(' ', '_')
                ->toString(),
            'name' => $name,
            'description' => $this->faker->sentence(),
        ];
    }
}
