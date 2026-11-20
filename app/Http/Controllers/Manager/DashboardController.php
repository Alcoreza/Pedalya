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
            ->leftJoin('services as dashboard_services', 'dashboard_services.id', '=', 'bookings.service_id')
            ->select(['bookings.*', 'dashboard_services.name as dashboard_service_name'])
            ->addSelect([
                'dashboard_repair_status' => Repair::query()
                    ->select('status')
                    ->whereColumn('booking_id', 'bookings.id')
                    ->limit(1),
            ])
            ->where('booking_status', 'confirmed')
            ->whereBetween('appointment_at', [$dayStart, $dayEnd])
            ->orderBy('appointment_at')
            ->get();

        $confirmedToday = $todayBookings->count();
        $upcoming = Booking::query()
            ->where('booking_status', 'confirmed')
            ->where('appointment_at', '>', $dashboardDate->copy()->utc()->toIso8601String())
            ->count();
        // The schedule presents six daily start times. Manager-approved
        // overlapping appointments can exceed this slot count, so the figure
        // is a slot reference rather than a hard cap on confirmed bookings.
        $dailyCapacity = max(1, (int) config('booking.daily_capacity', 6));

        // One aggregate replaces separate remote counts for the review badge
        // and today's review total.
        $pending = Booking::query()
            ->selectRaw(
                "count(*) filter (where booking_status = 'pending' and processing_method = 'review_required') as total,
                 count(*) filter (where booking_status = 'pending' and processing_method = 'review_required' and created_at between ? and ?) as today",
                [$dayStart, $dayEnd],
            )
            ->first();
        $pendingReview = (int) ($pending->total ?? 0);
        $pendingToday = (int) ($pending->today ?? 0);
        ManagerPresenter::cachePendingBookings($pendingReview);

        // "Active" means the bike is physically in the workshop queue. The
        // Repairs page also lists awaiting_arrival under its Active tab; add
        // it here if you want the two screens to match exactly.
        $repairSummary = Repair::query()
            ->selectRaw(
                "count(*) filter (where status in ('at_shop', 'in_progress')) as active,
                 count(*) filter (where completed_at between ? and ?) as completed_this_week",
                [$weekStart, $weekEnd],
            )
            ->first();
        $activeRepairs = (int) ($repairSummary->active ?? 0);
        $completedThisWeek = (int) ($repairSummary->completed_this_week ?? 0);

        $requests = Booking::query()
            ->leftJoin('services as request_services', 'request_services.id', '=', 'bookings.service_id')
            ->select(['bookings.*', 'request_services.name as dashboard_service_name'])
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
                'service' => $booking->getAttribute('dashboard_service_name') ?? 'Repair service',
                'schedule' => ManagerPresenter::shopTime($booking->appointment_at)->format('M d · g:i A'),
                'status' => $booking->booking_status === 'confirmed' ? 'confirmed' : 'pending',
                'time' => ManagerPresenter::shopTime($booking->created_at)->diffForHumans(),
            ])
            ->all();

        $appointments = $todayBookings
            ->map(function (Booking $booking): array {
                $at = ManagerPresenter::shopTime($booking->appointment_at);
                $repair = $booking->getAttribute('dashboard_repair_status');

                return [
                    'time' => $at->format('h:i'),
                    'period' => $at->format('A'),
                    'customer' => $booking->customer_name,
                    'service' => $booking->getAttribute('dashboard_service_name') ?? 'Repair service',
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
            ->join('bookings', 'bookings.id', '=', 'repairs.booking_id')
            ->leftJoin('services', 'services.id', '=', 'bookings.service_id')
            ->where('repairs.status', 'in_progress')
            ->orderBy('repairs.started_at')
            ->limit(5)
            ->get(['repairs.status', 'bookings.reference', 'services.name as dashboard_service_name'])
            ->map(fn (Repair $repair): array => [
                'reference' => $repair->getAttribute('reference'),
                'service' => $repair->getAttribute('dashboard_service_name') ?? 'Repair service',
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
