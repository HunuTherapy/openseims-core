<?php

namespace Database\Factories;

use App\Models\AccommodationType;
use App\Models\Learner;
use App\Models\LearnerAccommodation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerAccommodation>
 */
class LearnerAccommodationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'learner_id' => Learner::factory(),
            'accommodation_type_id' => AccommodationType::factory(),
            'status' => 'requested',
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->optional()->date(),
            'notes' => $this->faker->sentence(),
        ];
    }
}
