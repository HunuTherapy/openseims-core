<?php

namespace App\Services;

use App\Models\WebhookLog;
use RuntimeException;

class WebhookLogger
{
    /**
     * Persist a webhook event payload if it has not already been logged.
     *
     * @param  array<string, mixed>  $payload
     * @return bool True if a new log entry was created; false if it already existed.
     *
     * @throws RuntimeException
     */
    public static function handle(array $payload, string $provider): bool
    {
        $eventId = self::extractEventId($payload, $provider);
        $eventType = self::extractEventType($payload, $provider);

        if (! $eventId) {
            throw new RuntimeException("Missing event ID for provider [$provider].");
        }

        if (WebhookLog::query()->where('event_id', $eventId)->where('provider', $provider)->exists()) {
            return false; // Already logged
        }

        WebhookLog::create([
            'event_id' => $eventId,
            'provider' => $provider,
            'event_type' => $eventType,
            'payload' => $payload,
        ]);

        return true;
    }

    /**
     * Extract the event ID from a provider-specific webhook payload.
     *
     * @param  array<string, mixed>  $payload
     */
    protected static function extractEventId(array $payload, string $provider): ?string
    {
        return 'kobo';
    }

    /**
     * Extract the event type from a provider-specific webhook payload.
     *
     * @param  array<string, mixed>  $payload
     */
    protected static function extractEventType(array $payload, string $provider): string
    {
        return $payload['event']['type'];
    }
}
