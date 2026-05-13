<?php

namespace Database\Factories;

use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WebhookLog>
 */
class WebhookLogsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => (string) Str::uuid(),
            'provider' => 'kobo',
            'event_type' => $this->faker->word(),
            'payload' => ['event' => ['type' => $this->faker->word()]],
        ];
    }
}
