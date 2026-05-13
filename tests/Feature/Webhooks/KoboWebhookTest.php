<?php

namespace Tests\Feature\Webhooks;

use App\Models\WebhookLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KoboWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_kobo_webhook_creates_log(): void
    {
        $payload = [
            'event' => [
                'type' => 'submission',
            ],
        ];

        $this->postJson('/webhook/kobo', $payload)
            ->assertOk()
            ->assertContent('1');

        $this->assertDatabaseHas(WebhookLog::class, [
            'provider' => 'kobo',
            'event_type' => 'submission',
        ]);
    }

    public function test_kobo_webhook_does_not_duplicate_logs(): void
    {
        $payload = [
            'event' => [
                'type' => 'submission',
            ],
        ];

        $this->postJson('/webhook/kobo', $payload)->assertOk();
        $this->postJson('/webhook/kobo', $payload)
            ->assertOk()
            ->assertContent('');

        $this->assertDatabaseCount('webhook_logs', 1);
    }

    public function test_kobo_webhook_rejects_invalid_payload(): void
    {
        $this->postJson('/webhook/kobo', ['event' => []])
            ->assertServerError();
    }
}
