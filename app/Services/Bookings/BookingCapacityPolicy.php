<?php

namespace App\Services\Bookings;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BookingCapacityPolicy
{
    public function assess(array $candidate, Collection $bookings): array
    {
        $startsAt = $candidate['appointment_at'];
        $duration = $this->durationFor($candidate['service']);
        $endsAt = $startsAt->copy()->addMinutes($duration);
        $capacity = $this->capacityFor($candidate['service'], $startsAt);

        $conflicts = $bookings
            ->filter(function (array $booking) use ($candidate, $startsAt, $endsAt): bool {
                if ($booking['reference'] === $candidate['reference'] || ($booking['booking_status']['key'] ?? null) !== 'confirmed') {
                    return false;
                }

                $otherStart = $booking['appointment_at'];
                $otherEnd = $otherStart->copy()->addMinutes($this->durationFor($booking['service']));

                return $otherStart->lt($endsAt) && $otherEnd->gt($startsAt);
            })
            ->values();

        $isAlreadyConfirmed = ($candidate['booking_status']['key'] ?? null) === 'confirmed';
        $occupied = $conflicts->count() + ($isAlreadyConfirmed ? 1 : 0);
        $occupancyWithRequest = $isAlreadyConfirmed ? $occupied : $occupied + 1;

        return [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'duration_minutes' => $duration,
            'capacity' => $capacity,
            'confirmed_count' => $occupied,
            'occupancy_with_request' => $occupancyWithRequest,
            'has_capacity' => $occupancyWithRequest <= $capacity,
            'conflicts' => $conflicts->map(fn (array $booking): array => [
                'reference' => $booking['reference'],
                'customer' => $booking['customer']['name'],
                'service' => $booking['service'],
                'appointment_at' => $booking['appointment_at'],
                'ends_at' => $booking['appointment_at']->copy()->addMinutes($this->durationFor($booking['service'])),
            ]),
        ];
    }

    public function decorate(Collection $bookings): Collection
    {
        return $bookings->map(function (array $booking) use ($bookings): array {
            $booking['schedule_assessment'] = $this->assess($booking, $bookings);

            return $booking;
        });
    }

    public function durationFor(string $service): int
    {
        $rule = config('booking.services.'.Str::slug($service), []);

        return max(1, (int) ($rule['duration_minutes'] ?? config('booking.default_duration_minutes', 60)));
    }

    public function capacityFor(string $service, CarbonInterface $startsAt): int
    {
        $serviceKey = Str::slug($service);
        $serviceRule = config("booking.services.{$serviceKey}", []);

        if (isset($serviceRule['capacity'])) {
            return max(1, (int) $serviceRule['capacity']);
        }

        foreach (config('booking.capacity_overrides', []) as $rule) {
            if ($this->matchesOverride($rule, $serviceKey, $startsAt)) {
                return max(1, (int) $rule['capacity']);
            }
        }

        return max(1, (int) config('booking.default_capacity', 2));
    }

    private function matchesOverride(array $rule, string $serviceKey, CarbonInterface $startsAt): bool
    {
        $days = $rule['days'] ?? [];
        $services = $rule['services'] ?? [];
        $time = $startsAt->format('H:i');

        return ($days === [] || in_array($startsAt->dayOfWeekIso, $days, true))
            && ($services === [] || in_array($serviceKey, $services, true))
            && $time >= ($rule['start'] ?? '00:00')
            && $time < ($rule['end'] ?? '23:59');
    }
}
