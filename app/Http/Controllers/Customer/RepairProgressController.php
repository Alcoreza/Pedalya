<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepairProgressController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = $request->user()->bookings()
            ->with(['service', 'payment'])
            ->whereNotIn('booking_status', ['rejected', 'cancelled'])
            ->orderByDesc('appointment_at')
            ->get();

        return view('customer.repairs', ['bookings' => $bookings]);
    }

    public function bookingDetail(Request $request, string $reference): View
    {
        $booking = $this->bookingForCustomer($request, $reference);

        return view('customer.booking-detail', ['booking' => $booking]);
    }

    public function show(Request $request, string $reference): View
    {
        $booking = $this->bookingForCustomer($request, $reference);
        $status = $this->repairStatus($booking);

        return view('customer.repair', [
            'booking' => $booking,
            'repairStatus' => $status,
            'timeline' => $this->timeline($booking, $status),
        ]);
    }

    private function bookingForCustomer(Request $request, string $reference): Booking
    {
        return $request->user()->bookings()
            ->with(['service', 'payment'])
            ->where('reference', $reference)
            ->firstOrFail();
    }

    private function repairStatus(Booking $booking): array
    {
        $key = $booking->repair_status ?: match ($booking->booking_status) {
            'completed' => 'completed',
            'confirmed' => 'awaiting_arrival',
            default => 'not_started',
        };

        $labels = [
            'not_started' => 'Not started',
            'awaiting_arrival' => 'Awaiting bike arrival',
            'at_shop' => 'Bike at shop',
            'in_progress' => 'Repair in progress',
            'completed' => 'Repair completed',
            'claimed' => 'Bike claimed',
        ];

        return ['key' => $key, 'label' => $labels[$key] ?? 'Not started'];
    }

    private function timeline(Booking $booking, array $repairStatus): array
    {
        $approvalPending = $booking->booking_status === 'pending' && $booking->processing_method === 'review_required';
        $confirmed = in_array($booking->booking_status, ['confirmed', 'completed'], true);

        $events = [
            ['title' => 'Booking received', 'copy' => 'Your appointment request has been saved.', 'at' => $booking->created_at, 'state' => 'complete'],
            [
                'title' => $approvalPending ? 'Manager review' : ($booking->booking_status === 'rejected' ? 'Request not approved' : 'Appointment confirmed'),
                'copy' => $approvalPending ? 'The manager is reviewing this date and time.' : ($confirmed ? 'Your appointment is confirmed.' : 'Booking status: '.str_replace('_', ' ', $booking->booking_status).'.'),
                'at' => $confirmed ? $booking->created_at : null,
                'state' => $approvalPending ? 'current' : ($confirmed || $booking->booking_status === 'rejected' ? 'complete' : 'upcoming'),
            ],
            ['title' => 'Bike arrived at shop', 'copy' => $booking->repair_arrived_by ? 'Recorded by '.$booking->repair_arrived_by.'.' : 'The workshop will update this when your bike is received.', 'at' => $booking->repair_arrived_at, 'state' => $booking->repair_arrived_at ? 'complete' : ($repairStatus['key'] === 'awaiting_arrival' ? 'current' : 'upcoming')],
            ['title' => 'Repair work started', 'copy' => $booking->repair_started_by ? 'Recorded by '.$booking->repair_started_by.'.' : 'Repair work begins after the bike arrives and payment is verified.', 'at' => $booking->repair_started_at, 'state' => $booking->repair_started_at ? 'complete' : ($repairStatus['key'] === 'at_shop' ? 'current' : 'upcoming')],
            ['title' => 'Repair completed', 'copy' => $booking->repair_completed_by ? 'Your bike is ready for pickup.' : 'We will update this when the repair is finished.', 'at' => $booking->repair_completed_at, 'state' => $booking->repair_completed_at ? 'complete' : ($repairStatus['key'] === 'in_progress' ? 'current' : 'upcoming')],
            ['title' => 'Bike picked up', 'copy' => $booking->repair_claimed_by ? 'Pickup recorded by '.$booking->repair_claimed_by.'.' : 'This step is updated after you collect your bike.', 'at' => $booking->repair_claimed_at, 'state' => $booking->repair_claimed_at ? 'complete' : ($repairStatus['key'] === 'completed' ? 'current' : 'upcoming')],
        ];

        return array_map(function (array $event): array {
            $event['date_label'] = $event['at']
                ? $event['at']->setTimezone(config('booking.timezone'))->format('M j, Y · g:i A')
                : ($event['state'] === 'current' ? 'In progress' : 'Upcoming');

            return $event;
        }, $events);
    }
}
