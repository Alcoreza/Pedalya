<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Bookings\BookingCapacityPolicy;
use App\Services\Manager\ActivityLogger;
use App\Services\Manager\CalendarSyncRunner;
use App\Services\Manager\ManagerPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingsController extends Controller
{
    public function index(Request $request, BookingCapacityPolicy $capacityPolicy): View
    {
        $manager = ManagerPresenter::manager($request->user());
        // The capacity policy needs the full set to detect slot conflicts, so
        // filtering stays in memory on the decorated records (as before).
        $bookings = $capacityPolicy->decorate($this->bookingRecords());
        $filters = [
            'search' => trim((string) $request->string('search')),
            'booking_status' => (string) $request->string('booking_status'),
            'processing_method' => (string) $request->string('processing_method'),
            'payment_status' => (string) $request->string('payment_status'),
            'calendar_status' => (string) $request->string('calendar_status'),
        ];

        $filteredBookings = $bookings
            ->when($filters['search'] !== '', function (Collection $records) use ($filters): Collection {
                $search = Str::lower($filters['search']);

                return $records->filter(fn (array $booking): bool => Str::contains(Str::lower(implode(' ', [
                    $booking['reference'], $booking['customer']['name'], $booking['customer']['email'],
                    $booking['service'], $booking['notes'] ?? '', $booking['processing_method']['label'],
                ])), $search));
            })
            ->when($filters['booking_status'] !== '', fn (Collection $records): Collection => $records->where('booking_status.key', $filters['booking_status']))
            ->when($filters['processing_method'] !== '', fn (Collection $records): Collection => $records->where('processing_method.key', $filters['processing_method']))
            ->when($filters['payment_status'] !== '', fn (Collection $records): Collection => $records->where('payment_status.key', $filters['payment_status']))
            ->when($filters['calendar_status'] !== '', fn (Collection $records): Collection => $records->where('calendar_status.key', $filters['calendar_status']))
            ->values();

        $selectedReference = (string) $request->string('booking');
        $selectedBooking = $bookings->firstWhere('reference', $selectedReference) ?? $filteredBookings->first() ?? $bookings->first();

        return view('manager.bookings', [
            'manager' => $manager,
            'navigation' => ['pending_bookings' => $bookings->where('booking_status.key', 'review_required')->count()],
            'summary' => [
                'all' => $bookings->count(),
                'automatic' => $bookings->where('booking_status.key', 'confirmed')->where('processing_method.key', 'automatic')->count(),
                'review' => $bookings->where('booking_status.key', 'review_required')->count(),
                'calendar_follow_up' => $bookings->whereIn('calendar_status.key', ['pending', 'failed'])->count(),
            ],
            'filters' => $filters,
            'filterOptions' => [
                'booking' => ['review_required' => 'Schedule review', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected'],
                'processing' => ['automatic' => 'Automatic', 'review_required' => 'Schedule review', 'manual' => 'Manager reviewed'],
                'payment' => ['pending' => 'Pending', 'successful' => 'Successful', 'failed' => 'Failed'],
                'calendar' => ['pending' => 'Pending', 'synchronized' => 'Synchronized', 'failed' => 'Failed', 'not_applicable' => 'Not applicable'],
            ],
            'bookings' => $filteredBookings,
            'selectedBooking' => $selectedBooking,
            'defaultCapacity' => (int) config('booking.default_capacity', 1),
        ]);
    }

    public function decide(Request $request, string $booking, CalendarSyncRunner $calendarSync): RedirectResponse
    {
        $validated = $request->validate(['decision' => ['required', Rule::in(['confirmed', 'rejected'])]]);
        $user = $request->user();
        $confirmed = $validated['decision'] === 'confirmed';

        // Lock the row so two managers cannot decide the same booking at once.
        $record = DB::transaction(function () use ($booking, $validated, $user, $confirmed): Booking {
            $record = Booking::query()->where('reference', $booking)->lockForUpdate()->firstOrFail();
            abort_unless($record->processing_method === 'review_required' && $record->booking_status === 'pending', 422, 'This booking no longer requires review.');

            $record->update([
                'booking_status' => $validated['decision'],
                'processing_method' => 'manual',
            ]);

            ActivityLogger::record(
                eventKey: $confirmed ? 'booking.confirmed' : 'booking.rejected',
                category: 'booking',
                result: 'successful',
                title: $confirmed ? 'Booking request confirmed' : 'Booking request rejected',
                summary: $confirmed ? 'The manager accepted the customer booking request.' : 'The manager rejected the request without creating a calendar event.',
                detail: $confirmed
                    ? 'The booking decision was saved first. Calendar synchronization was started as a separate operation.'
                    : 'The booking status changed to rejected. Calendar synchronization remained not applicable.',
                booking: $record,
                metadata: [
                    'Previous status' => 'Pending review',
                    'Booking status' => $confirmed ? 'Confirmed' : 'Rejected',
                    ($confirmed ? 'Calendar action' : 'Calendar event created') => $confirmed ? 'Requested' : 'No',
                ],
                actor: $user,
                source: 'manager',
            );

            return $record;
        });

        // Runs after the decision is committed, so a calendar failure can
        // never undo or block the confirmation.
        if ($confirmed) {
            $calendarSync->run($record, $user);
        }

        $managerName = $user?->name ?? 'Manager';

        return redirect()->route('manager.bookings.index', ['booking' => $booking])->with(
            'booking_notice',
            $confirmed
                ? "{$booking} was confirmed by {$managerName}."
                : "{$booking} was rejected by {$managerName}."
        );
    }

    private function bookingRecords(): Collection
    {
        $bookings = Booking::query()
            ->with(['service', 'payment'])
            ->where(function ($query): void {
                $query->whereIn('booking_status', ['confirmed', 'rejected'])
                    ->orWhere(fn ($q) => $q->where('booking_status', 'pending')->where('processing_method', 'review_required'));
            })
            ->latest('created_at')
            ->get();

        $repairStatuses = ManagerPresenter::repairStatuses($bookings->pluck('id'));

        return $bookings
            ->map(fn (Booking $booking): array => ManagerPresenter::booking($booking, $repairStatuses[$booking->id] ?? null))
            ->values();
    }
}
