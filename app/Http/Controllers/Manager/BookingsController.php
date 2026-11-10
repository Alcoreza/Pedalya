<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingsController extends Controller
{
    public function index(Request $request): View
    {
        $managerName = auth()->user()?->name ?? 'Elmer Daniel';
        $bookings = $this->bookingRecords();
        $overrides = $request->session()->get('manager_booking_overrides', []);

        $bookings = $bookings->map(function (array $booking) use ($overrides): array {
            $override = $overrides[$booking['reference']] ?? [];

            return array_replace($booking, $override);
        });

        $filters = [
            'search' => trim((string) $request->string('search')),
            'booking_status' => (string) $request->string('booking_status'),
            'payment_status' => (string) $request->string('payment_status'),
            'calendar_status' => (string) $request->string('calendar_status'),
        ];

        $filteredBookings = $bookings
            ->when($filters['search'] !== '', function (Collection $records) use ($filters): Collection {
                $search = Str::lower($filters['search']);

                return $records->filter(fn (array $booking): bool => Str::contains(
                    Str::lower(implode(' ', [
                        $booking['reference'],
                        $booking['customer']['name'],
                        $booking['customer']['email'],
                        $booking['service'],
                    ])),
                    $search,
                ));
            })
            ->when($filters['booking_status'] !== '', fn (Collection $records): Collection => $records->where('booking_status.key', $filters['booking_status']))
            ->when($filters['payment_status'] !== '', fn (Collection $records): Collection => $records->where('payment_status.key', $filters['payment_status']))
            ->when($filters['calendar_status'] !== '', fn (Collection $records): Collection => $records->where('calendar_status.key', $filters['calendar_status']))
            ->values();

        $selectedReference = (string) $request->string('booking');
        $selectedBooking = $bookings->firstWhere('reference', $selectedReference)
            ?? $filteredBookings->first()
            ?? $bookings->first();

        return view('manager.bookings', [
            'manager' => [
                'name' => $managerName,
                'initials' => $this->initials($managerName),
                'role' => 'Manager',
            ],
            'navigation' => [
                'pending_bookings' => $bookings->where('booking_status.key', 'pending')->count(),
            ],
            'summary' => [
                'all' => $bookings->count(),
                'pending' => $bookings->where('booking_status.key', 'pending')->count(),
                'confirmed' => $bookings->where('booking_status.key', 'confirmed')->count(),
                'calendar_follow_up' => $bookings->whereIn('calendar_status.key', ['pending', 'failed'])->count(),
            ],
            'filters' => $filters,
            'filterOptions' => [
                'booking' => ['pending' => 'Pending review', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected'],
                'payment' => ['pending' => 'Pending', 'successful' => 'Successful', 'failed' => 'Failed'],
                'calendar' => ['pending' => 'Pending', 'synchronized' => 'Synchronized', 'failed' => 'Failed', 'not_applicable' => 'Not applicable'],
            ],
            'bookings' => $filteredBookings,
            'selectedBooking' => $selectedBooking,
        ]);
    }

    public function decide(Request $request, string $booking): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['confirmed', 'rejected'])],
        ]);

        abort_unless($this->bookingRecords()->contains('reference', $booking), 404);

        $overrides = $request->session()->get('manager_booking_overrides', []);
        $isConfirmed = $validated['decision'] === 'confirmed';

        // Temporary session persistence keeps the frontend flow testable.
        // Replace this assignment with the authorized booking decision service
        // once the Supabase-backed repository and Calendar adapter are connected.
        $overrides[$booking] = [
            'booking_status' => [
                'key' => $validated['decision'],
                'label' => $isConfirmed ? 'Confirmed' : 'Rejected',
            ],
            'calendar_status' => [
                'key' => $isConfirmed ? 'pending' : 'not_applicable',
                'label' => $isConfirmed ? 'Pending sync' : 'Not applicable',
            ],
        ];

        $request->session()->put('manager_booking_overrides', $overrides);

        return redirect()
            ->route('manager.bookings.index', ['booking' => $booking])
            ->with('booking_notice', $isConfirmed
                ? "{$booking} was confirmed. Calendar synchronization remains a separate backend operation."
                : "{$booking} was rejected and will not create a calendar event.");
    }

    private function bookingRecords(): Collection
    {
        return collect([
            [
                'reference' => 'PDL-1048',
                'customer' => ['name' => 'Ana Reyes', 'initials' => 'AR', 'email' => 'ana.reyes@example.com', 'phone' => '+63 917 000 1048'],
                'service' => 'Brake inspection',
                'submitted_at' => now()->subMinutes(12),
                'appointment_at' => now()->setTime(14, 30),
                'booking_status' => ['key' => 'pending', 'label' => 'Pending review'],
                'payment_status' => ['key' => 'pending', 'label' => 'Pending'],
                'calendar_status' => ['key' => 'not_applicable', 'label' => 'Not applicable'],
                'repair_status' => null,
                'calendar_event_id' => null,
            ],
            [
                'reference' => 'PDL-1047',
                'customer' => ['name' => 'Marco Lim', 'initials' => 'ML', 'email' => 'marco.lim@example.com', 'phone' => '+63 917 000 1047'],
                'service' => 'Wheel alignment',
                'submitted_at' => now()->subMinutes(38),
                'appointment_at' => now()->addDay()->setTime(10, 0),
                'booking_status' => ['key' => 'pending', 'label' => 'Pending review'],
                'payment_status' => ['key' => 'pending', 'label' => 'Pending'],
                'calendar_status' => ['key' => 'not_applicable', 'label' => 'Not applicable'],
                'repair_status' => null,
                'calendar_event_id' => null,
            ],
            [
                'reference' => 'PDL-1046',
                'customer' => ['name' => 'Bea Cruz', 'initials' => 'BC', 'email' => 'bea.cruz@example.com', 'phone' => '+63 917 000 1046'],
                'service' => 'General tune-up',
                'submitted_at' => now()->subHours(2),
                'appointment_at' => now()->addDay()->setTime(13, 0),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'successful', 'label' => 'Successful'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => null,
                'calendar_event_id' => 'gcal_pdl_1046',
            ],
            [
                'reference' => 'PDL-1045',
                'customer' => ['name' => 'Nico Santos', 'initials' => 'NS', 'email' => 'nico.santos@example.com', 'phone' => '+63 917 000 1045'],
                'service' => 'Chain replacement',
                'submitted_at' => now()->subHours(4),
                'appointment_at' => now()->addDays(2)->setTime(9, 30),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'pending', 'label' => 'Pending'],
                'calendar_status' => ['key' => 'failed', 'label' => 'Failed'],
                'repair_status' => null,
                'calendar_event_id' => null,
            ],
            [
                'reference' => 'PDL-1044',
                'customer' => ['name' => 'Ivan Co', 'initials' => 'IC', 'email' => 'ivan.co@example.com', 'phone' => '+63 917 000 1044'],
                'service' => 'Wheel alignment',
                'submitted_at' => now()->subDay(),
                'appointment_at' => now()->addDays(3)->setTime(16, 0),
                'booking_status' => ['key' => 'rejected', 'label' => 'Rejected'],
                'payment_status' => ['key' => 'failed', 'label' => 'Failed'],
                'calendar_status' => ['key' => 'not_applicable', 'label' => 'Not applicable'],
                'repair_status' => null,
                'calendar_event_id' => null,
            ],
            [
                'reference' => 'PDL-1039',
                'customer' => ['name' => 'Mia Flores', 'initials' => 'MF', 'email' => 'mia.flores@example.com', 'phone' => '+63 917 000 1039'],
                'service' => 'General tune-up',
                'submitted_at' => now()->subDays(4),
                'appointment_at' => now()->subDay()->setTime(11, 0),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'successful', 'label' => 'Successful'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => ['key' => 'completed', 'label' => 'Completed'],
                'calendar_event_id' => 'gcal_pdl_1039',
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
