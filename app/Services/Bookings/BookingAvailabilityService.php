<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingAvailabilityService
{
    public function dateOptions(Service $service): Collection
    {
        $today = CarbonImmutable::now(config('booking.timezone'))->startOfDay();
        $lastDate = $today->endOfMonth();
        $reservedTimes = Booking::query()
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->where('appointment_at', '>=', $today->utc()->toIso8601String())
            ->where('appointment_at', '<', $lastDate->addDay()->utc()->toIso8601String())
            ->get(['appointment_at'])
            ->mapWithKeys(fn (Booking $booking): array => [
                $booking->appointment_at->setTimezone(config('booking.timezone'))->format('Y-m-d H:i') => true,
            ]);
        $dates = collect();

        for ($date = $today; $date->lessThanOrEqualTo($lastDate); $date = $date->addDay()) {
            if (! in_array($date->dayOfWeekIso, config('booking.open_days'), true)) {
                continue;
            }

            $slots = $this->availableSlots($service, $date, $reservedTimes);
            $dates->push([
                'date' => $date,
                'key' => $date->format('Y-m-d'),
                'weekday' => $date->format('D'),
                'day' => $date->format('d'),
                'month_label' => $date->format('F Y'),
                'long_label' => $date->format('l, F j'),
                'slots' => $slots,
                'available_slots' => count($slots),
                'auto_confirm_slots' => collect($slots)->where('requires_review', false)->count(),
                'review_slots' => collect($slots)->where('requires_review', true)->count(),
            ]);
        }

        return $dates;
    }

    public function availableSlots(Service $service, CarbonImmutable $date, Collection $reservedTimes): array
    {
        if (! in_array($date->dayOfWeekIso, config('booking.open_days'), true)) {
            return [];
        }

        $now = CarbonImmutable::now(config('booking.timezone'));
        $closeAt = $date->setTimeFromTimeString(config('booking.closing_time'));
        $interval = (int) config('booking.slot_interval_minutes');
        $duration = (int) $service->duration_minutes;
        $slots = [];

        for (
            $start = $date->setTimeFromTimeString(config('booking.opening_time'));
            $start->addMinutes($duration)->lessThanOrEqualTo($closeAt);
            $start = $start->addMinutes($interval)
        ) {
            if ($start->lessThanOrEqualTo($now->addMinutes((int) config('booking.minimum_notice_minutes')))) {
                continue;
            }

            $slots[] = [
                'time' => $start->format('H:i'),
                'label' => $start->format('g:i A'),
                'requires_review' => $reservedTimes->has($start->format('Y-m-d H:i')),
            ];
        }

        return $slots;
    }

    public function isAvailable(Service $service, CarbonImmutable $startsAt): bool
    {
        $date = $startsAt->startOfDay();
        $today = CarbonImmutable::now(config('booking.timezone'))->startOfDay();
        $lastDate = $today->endOfMonth();

        if (! in_array($date->dayOfWeekIso, config('booking.open_days'), true)
            || $date->lessThan($today)
            || $date->greaterThan($lastDate)
            || $startsAt->lessThanOrEqualTo(CarbonImmutable::now(config('booking.timezone'))->addMinutes((int) config('booking.minimum_notice_minutes')))) {
            return false;
        }

        $isSlotStart = in_array($startsAt->format('H:i'), $this->slotStartTimes($service, $date), true);

        return $isSlotStart;
    }

    /** Whether an active booking already owns this exact date and start time. */
    public function requiresManagerReview(CarbonImmutable $startsAt): bool
    {
        return Booking::query()
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->where('appointment_at', $startsAt->utc()->toIso8601String())
            ->exists();
    }

    public function slotStartTimes(Service $service, CarbonImmutable $date): array
    {
        $closeAt = $date->setTimeFromTimeString(config('booking.closing_time'));
        $duration = (int) $service->duration_minutes;
        $interval = (int) config('booking.slot_interval_minutes');
        $times = [];

        for (
            $start = $date->setTimeFromTimeString(config('booking.opening_time'));
            $start->addMinutes($duration)->lessThanOrEqualTo($closeAt);
            $start = $start->addMinutes($interval)
        ) {
            $times[] = $start->format('H:i');
        }

        return $times;
    }

}
