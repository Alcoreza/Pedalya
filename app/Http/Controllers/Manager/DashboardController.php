<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Repair;
use App\Services\Manager\ManagerPresenter;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // Every "today" and "this week" boundary is computed in the shop timezone.
        $dashboardDate = ManagerPresenter::now();
        $manager = ManagerPresenter::manager(auth()->user());

        $dayStart = $dashboardDate->copy()->startOfDay()->toIso8601String();
        $dayEnd = $dashboardDate->copy()->endOfDay()->toIso8601String();
        $weekStart = $dashboardDate->copy()->startOfWeek(CarbonInterface::MONDAY)->toIso8601String();
        $weekEnd = $dashboardDate->copy()->endOfWeek(CarbonInterface::SUNDAY)->toIso8601String();

        $todayBookings = Booking::query()
            ->with('service')
            ->where('booking_status', 'confirmed')
            ->whereBetween('appointment_at', [$dayStart, $dayEnd])
            ->orderBy('appointment_at')
            ->get();
        $repairStatuses = ManagerPresenter::repairStatuses($todayBookings->pluck('id'));

        $confirmedToday = $todayBookings->count();
        $upcoming = $todayBookings->filter(fn (Booking $booking): bool => $booking->appointment_at->gt($dashboardDate))->count();
        $dailyCapacity = max(0, (int) config('booking.daily_capacity', 8));

        $pendingReview = ManagerPresenter::pendingBookings();
        $pendingToday = Booking::query()
            ->where('booking_status', 'pending')
            ->where('processing_method', 'review_required')
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->count();

        // "Active" means the bike is physically in the workshop queue. The
        // Repairs page also lists awaiting_arrival under its Active tab; add
        // it here if you want the two screens to match exactly.
        $activeRepairs = Repair::query()->whereIn('status', ['at_shop', 'in_progress'])->count();
        $completedThisWeek = Repair::query()->whereBetween('completed_at', [$weekStart, $weekEnd])->count();

        $requests = Booking::query()
            ->with('service')
            ->where(function ($query): void {
                $query->where('booking_status', 'confirmed')
                    ->orWhere(fn ($q) => $q->where('booking_status', 'pending')->where('processing_method', 'review_required'));
            })
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(fn (Booking $booking): array => [
                'reference' => $booking->reference,
                'customer' => $booking->customer_name,
                'initials' => ManagerPresenter::initials($booking->customer_name),
                'service' => $booking->service?->name ?? 'Repair service',
                'schedule' => ManagerPresenter::shopTime($booking->appointment_at)->format('M d · g:i A'),
                'status' => $booking->booking_status === 'confirmed' ? 'confirmed' : 'pending',
                'time' => ManagerPresenter::shopTime($booking->created_at)->diffForHumans(),
            ])
            ->all();

        $appointments = $todayBookings
            ->map(function (Booking $booking) use ($repairStatuses): array {
                $at = ManagerPresenter::shopTime($booking->appointment_at);
                $repair = $repairStatuses[$booking->id] ?? null;

                return [
                    'time' => $at->format('h:i'),
                    'period' => $at->format('A'),
                    'customer' => $booking->customer_name,
                    'service' => $booking->service?->name ?? 'Repair service',
                    'reference' => $booking->reference,
                    // Repair finished -> done, bike in the workshop -> current,
                    // anything else (including no repair row yet) -> next.
                    'state' => match ($repair) {
                        'completed', 'claimed' => 'done',
                        'at_shop', 'in_progress' => 'current',
                        default => 'next',
                    },
                ];
            })
            ->all();

        $repairs = Repair::query()
            ->with('booking.service')
            ->where('status', 'in_progress')
            ->orderBy('started_at')
            ->limit(5)
            ->get()
            ->map(fn (Repair $repair): array => [
                'reference' => $repair->booking->reference,
                'service' => $repair->booking->service?->name ?? 'Repair service',
                'status' => ManagerPresenter::repairStatus($repair->status)['label'],
            ])
            ->all();

        return view('manager.dashboard', [
            'manager' => $manager + ['first_name' => Str::before($manager['name'], ' ')],
            'dashboardDate' => $dashboardDate,
            'navigation' => [
                'pending_bookings' => $pendingReview,
            ],
            'dailySummary' => [
                'confirmed' => $confirmedToday,
                'capacity' => $dailyCapacity,
                'percentage' => $dailyCapacity > 0
                    ? min(100, (int) round(($confirmedToday / $dailyCapacity) * 100))
                    : 0,
                'upcoming' => $upcoming,
                'active_repairs' => $activeRepairs,
            ],
            'stats' => [
                ['label' => 'Pending review', 'value' => $pendingReview, 'trend' => $pendingToday.' new today', 'tone' => 'lime'],
                ['label' => 'Confirmed today', 'value' => $confirmedToday, 'trend' => $upcoming.' upcoming', 'tone' => 'green'],
                ['label' => 'Active repairs', 'value' => $activeRepairs, 'trend' => 'Workshop queue', 'tone' => 'amber'],
                ['label' => 'Completed', 'value' => $completedThisWeek, 'trend' => 'This week', 'tone' => 'plain'],
            ],
            'requests' => $requests,
            'appointments' => $appointments,
            'repairs' => $repairs,
        ]);
    }
}
