<?php

namespace App\Services\Manager;

use App\Models\Booking;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Throwable;

/**
 * Runs one Google Calendar synchronization attempt for a confirmed booking.
 *
 * - Never reverses the booking confirmation.
 * - Writes into the connected manager account with a separate duplicate guard.
 * - Counts exactly one attempt per run, whether or not
 *   GoogleCalendarService also counts it.
 * - Logs the outcome to activity_events. No error messages or tokens are logged.
 */
final class CalendarSyncRunner
{
    public const MAX_ATTEMPTS = 3;

    public function __construct(private readonly GoogleCalendarService $calendar)
    {
    }

    /** @return string resulting calendar_status */
    public function run(Booking $booking, ?User $actor = null): string
    {
        $booking->refresh();
        if ($booking->booking_status !== 'confirmed' || ! $this->calendar->managerConnection()) {
            return 'not_applicable';
        }

        $before = (int) $booking->manager_calendar_sync_attempts;

        if (filled($booking->manager_calendar_event_id)) {
            $booking->forceFill(['manager_calendar_status' => 'synchronized'])->save();
            $this->log($booking, 'calendar.sync.completed', 'successful', $before);

            return 'synchronized';
        }

        $booking->loadMissing('user.googleCalendarConnection');

        // No provider request is made without a connected calendar, so this
        // must not consume a retry attempt or be reported as a failed sync.
        if (! $booking->user?->googleCalendarConnection) {
            $booking->forceFill(['calendar_status' => 'not_applicable'])->save();

            return 'not_applicable';
        }

        $errored = false;

        try {
            $this->calendar->syncBookingToManager($booking);
            $booking->refresh();
        } catch (Throwable $e) {
            report($e);
            $errored = true;
            $booking->refresh();
        }

        $attempts = max((int) $booking->manager_calendar_sync_attempts, $before + 1);
        $status = $booking->manager_calendar_status;

        if ($errored && $status !== 'synchronized') {
            $status = 'failed';
        }

        $booking->forceFill(['manager_calendar_sync_attempts' => $attempts, 'manager_calendar_status' => $status])->save();

        if ($status === 'synchronized') {
            $this->log($booking, 'calendar.sync.completed', 'successful', $attempts);
        } elseif ($status === 'failed') {
            $this->log($booking, 'calendar.sync.failed', 'failed', $attempts, 'Temporary provider error');
        }

        return $status;
    }

    private function log(Booking $booking, string $key, string $result, int $attempt, ?string $errorType = null): void
    {
        $synced = $result === 'successful';

        ActivityLogger::record(
            eventKey: $key,
            category: 'calendar',
            result: $result,
            title: $synced ? 'Calendar event synchronized' : 'Calendar synchronization failed',
            summary: $synced
                ? 'The confirmed appointment was linked to the manager Google Calendar.'
                : 'The confirmed booking remains valid and needs calendar follow-up.',
            detail: $synced
                ? 'The provider event ID was stored against the booking after successful event creation.'
                : 'The provider did not complete the event creation request. No calendar event ID was stored, and the booking confirmation was not reversed.',
            booking: $booking,
            metadata: array_filter([
                'Attempt' => $attempt.' of '.self::MAX_ATTEMPTS,
                'Calendar status' => $synced ? 'Synchronized' : 'Failed',
                'Event ID stored' => filled($booking->manager_calendar_event_id) ? 'Yes' : 'No',
                'Error type' => $errorType,
                'Next action' => $synced || $attempt >= self::MAX_ATTEMPTS ? null : 'Safe retry',
            ], fn ($v) => $v !== null),
            provider: 'Google Calendar',
            providerReference: $booking->manager_calendar_event_id,
            source: 'system',
        );
    }
}
