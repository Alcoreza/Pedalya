<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $bookings = $user->bookings()->with(['service', 'repair'])->orderByDesc('created_at')->get();
        $isRepairComplete = fn ($booking): bool => $booking->booking_status === 'completed'
            || in_array($booking->repair?->status, ['completed', 'claimed'], true);
        $activeBookings = $bookings->filter(fn ($booking): bool => !$isRepairComplete($booking)
            && !in_array($booking->booking_status, ['rejected', 'cancelled'], true));
        $upcomingBooking = $bookings
            ->where('appointment_at', '>=', now())
            ->whereNotIn('booking_status', ['completed', 'rejected', 'cancelled'])
            ->reject(fn ($booking): bool => $isRepairComplete($booking))
            ->sortBy('appointment_at')
            ->first();

        return view('customer.dashboard', [
            'user' => $user,
            'firstName' => Str::of($user->name)->trim()->before(' '),
            'activeBookingsCount' => $activeBookings->count(),
            'attentionCount' => $bookings->filter(fn ($booking) => in_array($booking->booking_status, ['pending', 'rejected', 'cancelled'], true)
                || $booking->payment_status === 'pending')->count(),
            'completedCount' => $bookings->filter($isRepairComplete)->count(),
            'upcomingBooking' => $upcomingBooking,
            'recentBookings' => $bookings->take(3),
        ]);
    }
}
