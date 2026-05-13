<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SupervisionReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupervisionReport>
 */
class SupervisionReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'supervisor_id' => User::factory(),
            'supervisor_role' => 'Resource Teacher',
            'visit_date' => $this->faker->date(),
            'issues_found' => $this->faker->sentence(),
            'intervention_provided' => $this->faker->sentence(),
            'recipient_id' => User::factory(),
            'resolved' => false,
        ];
    }
}
