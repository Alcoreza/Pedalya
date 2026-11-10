<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $dashboardDate = now();
        $managerName = auth()->user()?->name ?? 'Elmer Daniel';

        // The view consumes this stable data contract. Replace these temporary
        // records with repository or query results when the database is connected.
        $confirmedToday = 6;
        $dailyCapacity = 8;

        return view('manager.dashboard', [
            'manager' => [
                'name' => $managerName,
                'first_name' => Str::before($managerName, ' '),
                'initials' => $this->initials($managerName),
                'role' => 'Manager',
            ],
            'dashboardDate' => $dashboardDate,
            'navigation' => [
                'pending_bookings' => 8,
            ],
            'dailySummary' => [
                'confirmed' => $confirmedToday,
                'capacity' => $dailyCapacity,
                'percentage' => $dailyCapacity > 0
                    ? min(100, (int) round(($confirmedToday / $dailyCapacity) * 100))
                    : 0,
                'upcoming' => 2,
                'active_repairs' => 4,
            ],
            'stats' => [
                ['label' => 'Pending review', 'value' => 8, 'trend' => '3 new today', 'tone' => 'lime'],
                ['label' => 'Confirmed today', 'value' => 6, 'trend' => '2 upcoming', 'tone' => 'green'],
                ['label' => 'Active repairs', 'value' => 4, 'trend' => 'Workshop queue', 'tone' => 'amber'],
                ['label' => 'Completed', 'value' => 12, 'trend' => 'This week', 'tone' => 'plain'],
            ],
            'requests' => [
                ['reference' => 'PDL-1048', 'customer' => 'Ana Reyes', 'initials' => 'AR', 'service' => 'Brake inspection', 'schedule' => $dashboardDate->copy()->setTime(14, 30)->format('M d · g:i A'), 'status' => 'pending', 'time' => '12 min ago'],
                ['reference' => 'PDL-1047', 'customer' => 'Marco Lim', 'initials' => 'ML', 'service' => 'Wheel alignment', 'schedule' => $dashboardDate->copy()->addDay()->setTime(10, 0)->format('M d · g:i A'), 'status' => 'pending', 'time' => '38 min ago'],
                ['reference' => 'PDL-1046', 'customer' => 'Bea Cruz', 'initials' => 'BC', 'service' => 'General tune-up', 'schedule' => $dashboardDate->copy()->addDay()->setTime(13, 0)->format('M d · g:i A'), 'status' => 'confirmed', 'time' => '1 hr ago'],
                ['reference' => 'PDL-1045', 'customer' => 'Nico Santos', 'initials' => 'NS', 'service' => 'Chain replacement', 'schedule' => $dashboardDate->copy()->addDays(2)->setTime(9, 30)->format('M d · g:i A'), 'status' => 'confirmed', 'time' => '2 hrs ago'],
            ],
            'appointments' => [
                ['time' => '09:00', 'period' => 'AM', 'customer' => 'Lia Mendoza', 'service' => 'General tune-up', 'reference' => 'PDL-1041', 'state' => 'done'],
                ['time' => '11:30', 'period' => 'AM', 'customer' => 'Paolo Tan', 'service' => 'Brake inspection', 'reference' => 'PDL-1042', 'state' => 'current'],
                ['time' => '02:30', 'period' => 'PM', 'customer' => 'Ana Reyes', 'service' => 'Brake inspection', 'reference' => 'PDL-1048', 'state' => 'next'],
                ['time' => '04:00', 'period' => 'PM', 'customer' => 'Ivan Co', 'service' => 'Wheel alignment', 'reference' => 'PDL-1044', 'state' => 'next'],
            ],
            'repairs' => [
                ['reference' => 'PDL-1038', 'service' => 'Drivetrain repair', 'status' => null],
                ['reference' => 'PDL-1040', 'service' => 'General tune-up', 'status' => null],
                ['reference' => 'PDL-1042', 'service' => 'Brake inspection', 'status' => null],
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
