<?php

namespace App\Services\Manager;

use App\Models\ActivityEvent;
use App\Models\Booking;
use App\Models\Repair;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Turns database models into the array contracts the manager Blade views
 * already consume, and holds helpers shared by every manager controller.
 */
final class ManagerPresenter
{
    public const BOOKING_LABELS = [
        'review_required' => 'Schedule review', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected',
        'completed' => 'Completed', 'cancelled' => 'Cancelled', 'pending' => 'Pending',
    ];

    public const PROCESSING_LABELS = [
        'review_required' => 'Schedule review', 'automatic' => 'Auto-confirmed', 'manual' => 'Manager reviewed',
    ];

    public const PAYMENT_LABELS = ['successful' => 'Successful', 'pending' => 'Pending', 'failed' => 'Failed'];

    public const CALENDAR_LABELS = [
        'not_applicable' => 'Not applicable', 'pending' => 'Pending sync',
        'synchronized' => 'Synchronized', 'failed' => 'Failed',
    ];

    public const REPAIR_LABELS = [
        'awaiting_arrival' => 'Awaiting arrival', 'at_shop' => 'Bike at shop',
        'in_progress' => 'Repair in progress', 'completed' => 'Repair completed', 'claimed' => 'Bike claimed',
    ];

    public static function timezone(): string
    {
        return (string) config('booking.timezone', config('app.timezone', 'UTC'));
    }

    public static function now(): Carbon
    {
        return Carbon::now(self::timezone());
    }

    /** Convert any stored timestamp to a Carbon in the shop's timezone. */
    public static function shopTime(mixed $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        $carbon = $value instanceof \DateTimeInterface ? Carbon::instance($value) : Carbon::parse($value);

        return $carbon->setTimezone(self::timezone());
    }

    public static function initials(string $name): string
    {
        return Str::of($name)->explode(' ')->filter()->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))->implode('');
    }

    public static function manager(?User $user): array
    {
        $name = $user?->name ?? 'Manager';

        return ['name' => $name, 'initials' => self::initials($name), 'role' => 'Manager'];
    }

    /** Sidebar badge: bookings waiting for a manager decision. */
    public static function pendingBookings(): int
    {
        return Booking::query()
            ->where('booking_status', 'pending')
            ->where('processing_method', 'review_required')
            ->count();
    }

    /**
     * @param  iterable<int>  $bookingIds
     * @return array<int, string> booking_id => repair status
     */
    public static function repairStatuses(iterable $bookingIds): array
    {
        $ids = collect($bookingIds)->unique()->values()->all();

        return $ids === [] ? [] : Repair::query()->whereIn('booking_id', $ids)->pluck('status', 'booking_id')->all();
    }

    /** @return array{key: string, label: string} */
    public static function repairStatus(string $key): array
    {
        return ['key' => $key, 'label' => self::REPAIR_LABELS[$key] ?? Str::headline($key)];
    }

    /** Booking -> the array contract used by Bookings, Schedule and Repairs. */
    public static function booking(Booking $booking, ?string $repairStatus = null): array
    {
        $reviewRequired = $booking->processing_method === 'review_required' && $booking->booking_status === 'pending';
        $bookingKey = $reviewRequired ? 'review_required' : $booking->booking_status;
        $processingKey = $reviewRequired ? 'review_required' : ($booking->processing_method ?: 'automatic');
        $paymentKey = $booking->payment_status === 'paid' ? 'successful' : $booking->payment_status;
        $calendarKey = $booking->manager_calendar_status ?: 'not_applicable';
        $name = $booking->customer_name;

        return [
            'id' => $booking->id,
            'reference' => $booking->reference,
            'customer' => [
                'name' => $name,
                'initials' => self::initials($name),
                'email' => $booking->customer_email,
                'phone' => $booking->customer_mobile_number,
            ],
            'service' => $booking->service?->name ?? 'Repair service',
            'duration_minutes' => (int) $booking->duration_minutes,
            'capacity_per_slot' => (int) ($booking->service?->capacity_per_slot ?? config('booking.default_capacity', 1)),
            'notes' => $booking->bike_notes,
            'submitted_at' => self::shopTime($booking->created_at),
            'appointment_at' => self::shopTime($booking->appointment_at),
            'booking_status' => ['key' => $bookingKey, 'label' => self::BOOKING_LABELS[$bookingKey] ?? ucfirst($bookingKey)],
            'processing_method' => ['key' => $processingKey, 'label' => self::PROCESSING_LABELS[$processingKey] ?? ucfirst($processingKey)],
            'review_reason' => $booking->review_reason,
            'payment_status' => ['key' => $paymentKey, 'label' => self::PAYMENT_LABELS[$paymentKey] ?? ucfirst($paymentKey)],
            'calendar_status' => ['key' => $calendarKey, 'label' => self::CALENDAR_LABELS[$calendarKey] ?? ucfirst($calendarKey)],
            'repair_status' => $repairStatus ? self::repairStatus($repairStatus) : null,
            'calendar_event_id' => $booking->calendar_event_id,
            'sync_attempts' => (int) $booking->manager_calendar_sync_attempts,
        ];
    }

    /** Repair (with booking.service, booking.payment and the *By users loaded) -> Repairs view contract. */
    public static function repair(Repair $repair): array
    {
        $data = self::booking($repair->booking, $repair->status);

        return [
            'reference' => $data['reference'],
            'customer' => $data['customer'],
            'service' => $data['service'],
            'notes' => $data['notes'],
            'appointment_at' => $data['appointment_at'],
            'booking_status' => $data['booking_status'],
            'payment_status' => $data['payment_status'],
            'calendar_status' => $data['calendar_status'],
            'repair_status' => $data['repair_status'],
            'arrived_at' => self::shopTime($repair->arrived_at),
            'arrived_by' => $repair->arrivedBy?->name,
            'started_at' => self::shopTime($repair->started_at),
            'started_by' => $repair->startedBy?->name,
            'completed_at' => self::shopTime($repair->completed_at),
            'completed_by' => $repair->completedBy?->name,
            'claimed_at' => self::shopTime($repair->claimed_at),
            'claimed_by' => $repair->claimedBy?->name,
        ];
    }

    /** Display id such as EVT-1001. Derived, never stored. */
    public static function activityId(int $id): string
    {
        return 'EVT-'.(1000 + $id);
    }

    /** EVT-1001 -> 1, or null when the value is not a display id. */
    public static function activityKey(?string $displayId): ?int
    {
        if ($displayId !== null && preg_match('/^EVT-(\d+)$/i', $displayId, $m) && (int) $m[1] > 1000) {
            return (int) $m[1] - 1000;
        }

        return null;
    }

    public static function activity(ActivityEvent $event): array
    {
        return [
            'id' => self::activityId($event->id),
            'event_key' => $event->event_key,
            'category' => ['key' => $event->category, 'label' => ucfirst($event->category)],
            'result' => ['key' => $event->result, 'label' => ucfirst($event->result)],
            'title' => $event->title,
            'summary' => $event->summary,
            'detail' => $event->detail,
            'booking_reference' => $event->booking_reference,
            'provider' => $event->provider,
            'provider_reference' => $event->provider_reference,
            'actor' => $event->actor_label,
            'source' => $event->source,
            'occurred_at' => self::shopTime($event->occurred_at),
            'metadata' => collect($event->metadata ?? [])
                ->map(fn ($field) => ['label' => (string) ($field['label'] ?? ''), 'value' => (string) ($field['value'] ?? '')])
                ->all(),
        ];
    }
}
