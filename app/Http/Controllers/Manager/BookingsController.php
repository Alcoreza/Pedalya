<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Bookings\BookingCapacityPolicy;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingsController extends Controller
{
    public function index(Request $request, BookingCapacityPolicy $capacityPolicy): View
    {
        $managerName = $request->user()?->name ?? 'Manager';
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
                ])), $search);
            })
            ->when($filters['booking_status'] !== '', fn (Collection $records): Collection => $records->where('booking_status.key', $filters['booking_status']))
            ->when($filters['processing_method'] !== '', fn (Collection $records): Collection => $records->where('processing_method.key', $filters['processing_method']))
            ->when($filters['payment_status'] !== '', fn (Collection $records): Collection => $records->where('payment_status.key', $filters['payment_status']))
            ->when($filters['calendar_status'] !== '', fn (Collection $records): Collection => $records->where('calendar_status.key', $filters['calendar_status']))
            ->values();

        $selectedReference = (string) $request->string('booking');
        $selectedBooking = $bookings->firstWhere('reference', $selectedReference) ?? $filteredBookings->first() ?? $bookings->first();

        return view('manager.bookings', [
            'manager' => ['name' => $managerName, 'initials' => $this->initials($managerName), 'role' => 'Manager'],
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

    public function decide(Request $request, string $booking, GoogleCalendarService $googleCalendar): RedirectResponse
    {
        $validated = $request->validate(['decision' => ['required', Rule::in(['confirmed', 'rejected'])]]);
        $record = Booking::query()->where('reference', $booking)->firstOrFail();
        abort_unless($record->processing_method === 'review_required' && $record->booking_status === 'pending', 422, 'This booking no longer requires review.');

        $record->update([
            'booking_status' => $validated['decision'],
            'processing_method' => 'manual',
        ]);

        if ($validated['decision'] === 'confirmed') {
            $googleCalendar->syncBooking($record);
        }

        $managerName = $request->user()?->name ?? 'Manager';
        return redirect()->route('manager.bookings.index', ['booking' => $booking])->with(
            'booking_notice',
            $validated['decision'] === 'confirmed'
                ? "{$booking} was confirmed by {$managerName}."
                : "{$booking} was rejected by {$managerName}."
        );
    }

    private function bookingRecords(): Collection
    {
        return Booking::query()->with(['service', 'payment'])->latest('created_at')->get()
            ->map(function (Booking $booking): array {
                $reviewRequired = $booking->processing_method === 'review_required' && $booking->booking_status === 'pending';
                $bookingKey = $reviewRequired ? 'review_required' : $booking->booking_status;
                $processingKey = $reviewRequired ? 'review_required' : ($booking->processing_method ?: 'automatic');
                $paymentKey = $booking->payment_status === 'paid' ? 'successful' : $booking->payment_status;
                $calendarKey = $booking->calendar_status ?: 'not_applicable';
                $name = $booking->customer_name;

                return [
                    'id' => $booking->id,
                    'reference' => $booking->reference,
                    'customer' => ['name' => $name, 'initials' => $this->initials($name), 'email' => $booking->customer_email, 'phone' => $booking->customer_mobile_number],
                    'service' => $booking->service?->name ?? 'Repair service',
                    'duration_minutes' => (int) $booking->duration_minutes,
                    'capacity_per_slot' => (int) ($booking->service?->capacity_per_slot ?? config('booking.default_capacity', 1)),
                    'notes' => $booking->bike_notes,
                    'submitted_at' => $booking->created_at,
                    'appointment_at' => $booking->appointment_at->setTimezone(config('booking.timezone')),
                    'booking_status' => ['key' => $bookingKey, 'label' => ['review_required' => 'Schedule review', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'pending' => 'Pending'][$bookingKey] ?? ucfirst($bookingKey)],
                    'processing_method' => ['key' => $processingKey, 'label' => ['review_required' => 'Schedule review', 'automatic' => 'Auto-confirmed', 'manual' => 'Manager reviewed'][$processingKey] ?? ucfirst($processingKey)],
                    'review_reason' => $booking->review_reason,
                    'payment_status' => ['key' => $paymentKey, 'label' => ['successful' => 'Successful', 'pending' => 'Pending', 'failed' => 'Failed'][$paymentKey] ?? ucfirst($paymentKey)],
                    'calendar_status' => ['key' => $calendarKey, 'label' => ['not_applicable' => 'Not applicable', 'pending' => 'Pending sync', 'synchronized' => 'Synchronized', 'failed' => 'Failed'][$calendarKey] ?? ucfirst($calendarKey)],
                    'repair_status' => null,
                    'calendar_event_id' => $booking->calendar_event_id,
                ];
            })
            ->filter(fn (array $booking): bool => in_array($booking['booking_status']['key'], ['review_required', 'confirmed', 'rejected'], true))
            ->values();
    }

    private function initials(string $name): string
    {
        return Str::of($name)->explode(' ')->filter()->take(2)->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))->implode('');
    }
}
