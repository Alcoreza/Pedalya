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
        $bookings = $this->bookingsForWindow(
            $today->setTimeFromTimeString(config('booking.opening_time')),
            $lastDate->setTimeFromTimeString(config('booking.closing_time')),
        );
        $dates = collect();

        for ($date = $today; $date->lessThanOrEqualTo($lastDate); $date = $date->addDay()) {
            if (! in_array($date->dayOfWeekIso, config('booking.open_days'), true)) {
                continue;
            }

            $slots = $this->availableSlots($service, $date, $bookings);
            $dates->push([
                'date' => $date,
                'key' => $date->format('Y-m-d'),
                'weekday' => $date->format('D'),
                'day' => $date->format('d'),
                'month_label' => $date->format('F Y'),
                'long_label' => $date->format('l, F j'),
                'slots' => $slots,
                'available_slots' => count($slots),
            ]);
        }

        return $dates;
    }

    public function availableSlots(Service $service, CarbonImmutable $date, ?Collection $bookings = null): array
    {
        if (! in_array($date->dayOfWeekIso, config('booking.open_days'), true)) {
            return [];
        }

        $now = CarbonImmutable::now(config('booking.timezone'));
        $closeAt = $date->setTimeFromTimeString(config('booking.closing_time'));
        $bookings ??= $this->bookingsForWindow(
            $date->setTimeFromTimeString(config('booking.opening_time')),
            $closeAt,
        );
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

            if ($this->hasCapacity($service, $start, $duration, $bookings)) {
                $slots[] = [
                    'time' => $start->format('H:i'),
                    'label' => $start->format('g:i A'),
                ];
            }
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

        return $isSlotStart && $this->hasCapacity($service, $startsAt, (int) $service->duration_minutes);
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

    private function hasCapacity(Service $service, CarbonImmutable $startsAt, int $duration, ?Collection $bookings = null): bool
    {
        $endsAt = $startsAt->addMinutes($duration);
        $occupied = $bookings === null
            ? $this->bookingsForWindow($startsAt, $endsAt)->count()
            : $bookings->filter(function (Booking $booking) use ($startsAt, $endsAt): bool {
                $bookingStartsAt = $booking->appointment_at;
                $bookingEndsAt = $bookingStartsAt->addMinutes((int) $booking->duration_minutes);

                return $bookingStartsAt->lessThan($endsAt) && $bookingEndsAt->greaterThan($startsAt);
            })->count();

        return $occupied < (int) $service->capacity_per_slot;
    }

    private function bookingsForWindow(CarbonImmutable $startsAt, CarbonImmutable $endsAt): Collection
    {
        return Booking::query()
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->where('appointment_at', '<', $endsAt->utc()->toIso8601String())
            ->whereRaw("appointment_at + (duration_minutes * INTERVAL '1 minute') > ?", [$startsAt->utc()->toIso8601String()])
            ->get(['appointment_at', 'duration_minutes']);
    }
}
