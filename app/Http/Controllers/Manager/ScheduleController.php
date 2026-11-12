<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'booking' => ['nullable', 'string', 'max:40'],
        ]);

        $selectedDate = isset($validated['date'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay()
            : now()->startOfDay();
        $weekStart = $selectedDate->copy()->startOfWeek(CarbonInterface::MONDAY);
        $managerName = auth()->user()?->name ?? 'Elmer Daniel';
        $appointments = $this->appointmentRecords($weekStart);
        $syncOverrides = $request->session()->get('manager_calendar_sync_overrides', []);

        $appointments = $appointments->map(function (array $appointment) use ($syncOverrides): array {
            $override = $syncOverrides[$appointment['reference']] ?? [];

            return array_replace($appointment, $override);
        });

        $days = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $appointments): array {
            $date = $weekStart->copy()->addDays($offset);
            $dayAppointments = $appointments
                ->filter(fn (array $appointment): bool => $appointment['appointment_at']->isSameDay($date))
                ->sortBy('appointment_at')
                ->values();

            return [
                'date' => $date,
                'is_today' => $date->isToday(),
                'appointment_count' => $dayAppointments->count(),
                'slots' => $dayAppointments
                    ->groupBy(fn (array $appointment): string => $appointment['appointment_at']->format('H:i'))
                    ->map(function (Collection $slotAppointments): array {
                        $firstAppointment = $slotAppointments->first();

                        return [
                            'time' => $firstAppointment['appointment_at'],
                            'appointments' => $slotAppointments->values(),
                            'is_shared' => $slotAppointments->count() > 1,
                        ];
                    })
                    ->values(),
            ];
        });

        $selectedReference = $validated['booking'] ?? null;
        $selectedAppointment = $appointments->firstWhere('reference', $selectedReference)
            ?? $appointments->sortBy('appointment_at')->first();

        return view('manager.schedule', [
            'manager' => [
                'name' => $managerName,
                'initials' => $this->initials($managerName),
                'role' => 'Manager',
            ],
            'navigation' => [
                'pending_bookings' => 2,
            ],
            'selectedDate' => $selectedDate,
            'weekStart' => $weekStart,
            'weekEnd' => $weekStart->copy()->endOfWeek(CarbonInterface::SUNDAY),
            'previousWeek' => $weekStart->copy()->subWeek(),
            'nextWeek' => $weekStart->copy()->addWeek(),
            'days' => $days,
            'selectedAppointment' => $selectedAppointment,
            'summary' => [
                'confirmed' => $appointments->count(),
                'today' => $appointments->filter(fn (array $appointment): bool => $appointment['appointment_at']->isToday())->count(),
                'synchronized' => $appointments->where('calendar_status.key', 'synchronized')->count(),
                'follow_up' => $appointments->whereIn('calendar_status.key', ['pending', 'failed'])->count(),
            ],
        ]);
    }

    public function retryCalendarSync(Request $request, string $booking): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $knownReferences = ['PDL-1041', 'PDL-1042', 'PDL-1053', 'PDL-1054', 'PDL-1046', 'PDL-1045', 'PDL-1050'];
        abort_unless(in_array($booking, $knownReferences, true), 404);

        $overrides = $request->session()->get('manager_calendar_sync_overrides', []);
        $defaultAttempts = $booking === 'PDL-1045' ? 1 : 0;
        $attempts = min(3, ((int) ($overrides[$booking]['sync_attempts'] ?? $defaultAttempts)) + 1);

        // Temporary session state keeps the frontend contract testable. Replace
        // this with the Calendar synchronization service/queued job. That service
        // must check for an existing event before each retry and stop after three.
        $overrides[$booking] = [
            'calendar_status' => ['key' => 'pending', 'label' => 'Pending sync'],
            'sync_attempts' => $attempts,
        ];
        $request->session()->put('manager_calendar_sync_overrides', $overrides);

        return redirect()
            ->route('manager.schedule.index', ['date' => $validated['date'], 'booking' => $booking])
            ->with('schedule_notice', "Calendar follow-up for {$booking} is queued. The confirmed booking remains unchanged.");
    }

    private function appointmentRecords(Carbon $weekStart): Collection
    {
        return collect([
            [
                'reference' => 'PDL-1041',
                'customer' => ['name' => 'Lia Mendoza', 'initials' => 'LM', 'email' => 'lia.mendoza@example.com', 'phone' => '+63 917 000 1041'],
                'service' => 'General tune-up',
                'appointment_at' => $weekStart->copy()->setTime(9, 0),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'successful', 'label' => 'Successful'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => null,
                'calendar_event_id' => 'gcal_pdl_1041',
                'sync_attempts' => 0,
            ],
            [
                'reference' => 'PDL-1042',
                'customer' => ['name' => 'Paolo Tan', 'initials' => 'PT', 'email' => 'paolo.tan@example.com', 'phone' => '+63 917 000 1042'],
                'service' => 'Brake inspection',
                'appointment_at' => $weekStart->copy()->addDay()->setTime(11, 30),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'pending', 'label' => 'Pending'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => null,
                'calendar_event_id' => 'gcal_pdl_1042',
                'sync_attempts' => 0,
            ],
            [
                'reference' => 'PDL-1053',
                'customer' => ['name' => 'Jules Aquino', 'initials' => 'JA', 'email' => 'jules.aquino@example.com', 'phone' => '+63 917 000 1053'],
                'service' => 'Safety inspection',
                'appointment_at' => $weekStart->copy()->addDay()->setTime(11, 30),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'successful', 'label' => 'Successful'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => null,
                'calendar_event_id' => 'gcal_pdl_1053',
                'sync_attempts' => 0,
            ],
            [
                'reference' => 'PDL-1054',
                'customer' => ['name' => 'Carlo Reyes', 'initials' => 'CR', 'email' => 'carlo.reyes@example.com', 'phone' => '+63 917 000 1054'],
                'service' => 'Wheel alignment',
                'appointment_at' => $weekStart->copy()->addDay()->setTime(14, 0),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'pending', 'label' => 'Pending'],
                'calendar_status' => ['key' => 'pending', 'label' => 'Pending sync'],
                'repair_status' => null,
                'calendar_event_id' => null,
                'sync_attempts' => 0,
            ],
            [
                'reference' => 'PDL-1046',
                'customer' => ['name' => 'Bea Cruz', 'initials' => 'BC', 'email' => 'bea.cruz@example.com', 'phone' => '+63 917 000 1046'],
                'service' => 'General tune-up',
                'appointment_at' => $weekStart->copy()->addDays(2)->setTime(13, 0),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'successful', 'label' => 'Successful'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => null,
                'calendar_event_id' => 'gcal_pdl_1046',
                'sync_attempts' => 0,
            ],
            [
                'reference' => 'PDL-1045',
                'customer' => ['name' => 'Nico Santos', 'initials' => 'NS', 'email' => 'nico.santos@example.com', 'phone' => '+63 917 000 1045'],
                'service' => 'Chain replacement',
                'appointment_at' => $weekStart->copy()->addDays(4)->setTime(10, 0),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'pending', 'label' => 'Pending'],
                'calendar_status' => ['key' => 'failed', 'label' => 'Failed'],
                'repair_status' => null,
                'calendar_event_id' => null,
                'sync_attempts' => 1,
            ],
            [
                'reference' => 'PDL-1050',
                'customer' => ['name' => 'Sam Villanueva', 'initials' => 'SV', 'email' => 'sam.villanueva@example.com', 'phone' => '+63 917 000 1050'],
                'service' => 'Brake inspection',
                'appointment_at' => $weekStart->copy()->addDays(5)->setTime(13, 30),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'pending', 'label' => 'Pending'],
                'calendar_status' => ['key' => 'pending', 'label' => 'Pending sync'],
                'repair_status' => null,
                'calendar_event_id' => null,
                'sync_attempts' => 0,
            ],
        ]);
    }

    private function initials(string $name): string
    {
        return Str::of($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }
}
