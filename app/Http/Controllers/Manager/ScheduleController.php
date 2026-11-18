<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Manager\ActivityLogger;
use App\Services\Manager\CalendarSyncRunner;
use App\Services\Manager\ManagerPresenter;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'booking' => ['nullable', 'string', 'max:40'],
        ]);

        // Week boundaries are computed in the shop timezone, then queried as
        // absolute instants, so late-Sunday bookings land in the right week.
        $timezone = ManagerPresenter::timezone();
        $selectedDate = isset($validated['date'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date'], $timezone)->startOfDay()
            : ManagerPresenter::now()->startOfDay();
        $weekStart = $selectedDate->copy()->startOfWeek(CarbonInterface::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(CarbonInterface::SUNDAY);
        $appointments = $this->appointmentRecords($weekStart, $weekEnd);

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
            'manager' => ManagerPresenter::manager($request->user()),
            'navigation' => [
                'pending_bookings' => ManagerPresenter::pendingBookings(),
            ],
            'selectedDate' => $selectedDate,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
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

    public function retryCalendarSync(Request $request, string $booking, CalendarSyncRunner $calendarSync): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $user = $request->user();
        $record = Booking::query()->where('reference', $booking)->firstOrFail();

        $back = fn (string $notice): RedirectResponse => redirect()
            ->route('manager.schedule.index', ['date' => $validated['date'], 'booking' => $booking])
            ->with('schedule_notice', $notice);

        // Lock, validate and mark pending in one transaction. Marking the row
        // pending also blocks a second manager from starting a parallel retry.
        $rejection = DB::transaction(function () use ($record, $user): ?string {
            $locked = Booking::query()->whereKey($record->id)->lockForUpdate()->firstOrFail();

            if ($locked->booking_status !== 'confirmed') {
                return 'Calendar follow-up is only available for confirmed bookings.';
            }

            if ($locked->calendar_status !== 'failed') {
                return "Calendar follow-up for {$locked->reference} is not needed right now.";
            }

            if ((int) $locked->calendar_sync_attempts >= CalendarSyncRunner::MAX_ATTEMPTS) {
                return 'The retry limit was reached. Contact support to link this appointment manually.';
            }

            $next = (int) $locked->calendar_sync_attempts + 1;
            $locked->forceFill(['calendar_status' => 'pending'])->save();

            ActivityLogger::record(
                eventKey: 'calendar.retry.queued',
                category: 'calendar',
                result: 'pending',
                title: 'Calendar retry queued',
                summary: 'A safe retry was requested for a confirmed booking.',
                detail: 'The synchronization service checks whether the event already exists before creating another event.',
                booking: $locked,
                metadata: [
                    'Attempt' => $next.' of '.CalendarSyncRunner::MAX_ATTEMPTS,
                    'Duplicate check' => 'Required',
                    'Booking status' => 'Confirmed',
                ],
                provider: 'Google Calendar',
                actor: $user,
                source: 'manager',
            );

            return null;
        });

        if ($rejection !== null) {
            return $back($rejection);
        }

        // The external call runs outside the transaction. Any failure is
        // recorded on the calendar fields and never touches booking_status.
        $status = $calendarSync->run($record, $user);

        return $back($status === 'synchronized'
            ? "Calendar follow-up for {$booking} completed. The confirmed booking remains unchanged."
            : "Calendar follow-up for {$booking} is queued. The confirmed booking remains unchanged.");
    }

    private function appointmentRecords(Carbon $weekStart, Carbon $weekEnd): Collection
    {
        $bookings = Booking::query()
            ->with(['service', 'payment'])
            ->where('booking_status', 'confirmed')
            ->whereBetween('appointment_at', [$weekStart->toIso8601String(), $weekEnd->toIso8601String()])
            ->orderBy('appointment_at')
            ->get();

        $repairStatuses = ManagerPresenter::repairStatuses($bookings->pluck('id'));

        return $bookings
            ->map(fn (Booking $booking): array => ManagerPresenter::booking($booking, $repairStatuses[$booking->id] ?? null))
            ->values();
    }
}
