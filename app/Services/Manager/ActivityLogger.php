<?php

namespace App\Services\Manager;

use App\Models\ActivityEvent;
use App\Models\Booking;
use App\Models\User;

/**
 * Writes rows to public.activity_events.
 *
 * Never pass passwords, tokens, API keys or raw provider payloads in any
 * argument. The Activity screen promises these are excluded.
 */
final class ActivityLogger
{
    /**
     * @param  array<string, string>|list<array{label: string, value: string}>  $metadata
     * @param  string  $source  manager|system|customer|provider
     * @param  string|null  $idempotencyKey  e.g. a webhook_event_id; duplicates are silently ignored
     */
    public static function record(
        string $eventKey,
        string $category,
        string $result,
        string $title,
        string $summary = '',
        string $detail = '',
        ?Booking $booking = null,
        array $metadata = [],
        string $provider = 'Pedalya',
        ?string $providerReference = null,
        ?User $actor = null,
        string $source = 'system',
        ?string $idempotencyKey = null,
    ): void {
        $fields = collect($metadata)
            ->map(fn ($value, $key) => is_array($value)
                ? ['label' => (string) ($value['label'] ?? ''), 'value' => (string) ($value['value'] ?? '')]
                : ['label' => (string) $key, 'value' => (string) $value])
            ->values()
            ->all();

        // insertOrIgnore = INSERT ... ON CONFLICT DO NOTHING, so a repeated
        // idempotency_key (duplicate webhook) never creates a second row.
        // occurred_at is left to the database default (now()).
        ActivityEvent::query()->insertOrIgnore([
            'event_key' => $eventKey,
            'category' => $category,
            'result' => $result,
            'title' => $title,
            'summary' => $summary,
            'detail' => $detail,
            'booking_id' => $booking?->id,
            'booking_reference' => $booking?->reference,
            'provider' => $provider,
            'provider_reference' => $providerReference,
            'actor_user_id' => $actor?->id,
            'actor_label' => $actor?->name ?? ($source === 'manager' ? 'Manager' : 'System process'),
            'source' => $source,
            'metadata' => json_encode($fields, JSON_UNESCAPED_UNICODE),
            'idempotency_key' => $idempotencyKey,
        ]);
    }
}
