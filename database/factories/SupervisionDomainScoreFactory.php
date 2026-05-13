<?php

namespace Database\Factories;

use App\Models\SupervisionDomainScore;
use App\Models\SupervisionReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupervisionDomainScore>
 */
class SupervisionDomainScoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supervision_report_id' => SupervisionReport::factory(),
            'domain_name' => $this->faker->words(2, true),
            'score' => $this->faker->numberBetween(1, 5),
        ];
    }
}
