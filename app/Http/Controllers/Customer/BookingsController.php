<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookingsController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['service', 'payment', 'repair'])
            ->orderByDesc('appointment_at')
            ->get();

        return view('customer.bookings', [
            'bookings' => $bookings,
            'summary' => [
                'total' => $bookings->count(),
                'attention' => $bookings->filter(fn ($booking) => in_array($booking->booking_status, ['pending', 'rejected', 'cancelled'], true)
                    || $booking->payment_status === 'pending')->count(),
                'completed' => $bookings->filter(fn ($booking) => $booking->booking_status === 'completed'
                    || in_array($booking->repair?->status, ['completed', 'claimed'], true))->count(),
            ],
        ]);
    }
}
