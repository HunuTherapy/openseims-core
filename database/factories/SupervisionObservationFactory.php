<?php

namespace Database\Factories;

use App\Models\SupervisionObservation;
use App\Models\SupervisionReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupervisionObservation>
 */
class SupervisionObservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supervision_report_id' => SupervisionReport::factory(),
            'issues_found' => $this->faker->sentence(),
            'intervention_provided' => $this->faker->sentence(),
            'deadline_date' => $this->faker->date(),
            'resolved' => false,
        ];
    }
}
