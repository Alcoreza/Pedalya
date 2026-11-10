<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RepairsController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:unrecorded,completed'],
            'repair' => ['nullable', 'string', 'max:40'],
        ]);

        $managerName = auth()->user()?->name ?? 'Elmer Daniel';
        $overrides = $request->session()->get('manager_repair_status_overrides', []);
        $repairs = $this->repairRecords()->map(function (array $repair) use ($overrides): array {
            return array_replace($repair, $overrides[$repair['reference']] ?? []);
        });

        $filters = [
            'search' => trim((string) ($validated['search'] ?? '')),
            'status' => (string) ($validated['status'] ?? ''),
        ];

        $filteredRepairs = $repairs
            ->when($filters['search'] !== '', function (Collection $records) use ($filters): Collection {
                $search = Str::lower($filters['search']);

                return $records->filter(fn (array $repair): bool => Str::contains(
                    Str::lower(implode(' ', [
                        $repair['reference'],
                        $repair['customer']['name'],
                        $repair['service'],
                    ])),
                    $search,
                ));
            })
            ->when($filters['status'] === 'completed', fn (Collection $records): Collection => $records->filter(fn (array $repair): bool => ($repair['repair_status']['key'] ?? null) === 'completed'))
            ->when($filters['status'] === 'unrecorded', fn (Collection $records): Collection => $records->filter(fn (array $repair): bool => $repair['repair_status'] === null))
            ->values();

        $selectedReference = $validated['repair'] ?? null;
        $selectedRepair = $repairs->firstWhere('reference', $selectedReference)
            ?? $filteredRepairs->first()
            ?? $repairs->first();

        return view('manager.repairs', [
            'manager' => [
                'name' => $managerName,
                'initials' => $this->initials($managerName),
                'role' => 'Manager',
            ],
            'navigation' => [
                'pending_bookings' => 2,
            ],
            'summary' => [
                'all' => $repairs->count(),
                'unrecorded' => $repairs->filter(fn (array $repair): bool => $repair['repair_status'] === null)->count(),
                'completed' => $repairs->filter(fn (array $repair): bool => ($repair['repair_status']['key'] ?? null) === 'completed')->count(),
                'completed_today' => $repairs->filter(fn (array $repair): bool => $repair['completed_at']?->isToday() === true)->count(),
            ],
            'filters' => $filters,
            'repairs' => $filteredRepairs,
            'selectedRepair' => $selectedRepair,
            'auditEvents' => $selectedRepair ? $this->auditEvents($selectedRepair) : collect(),
        ]);
    }

    public function markCompleted(Request $request, string $booking): RedirectResponse
    {
        $repair = $this->repairRecords()->firstWhere('reference', $booking);
        abort_unless($repair, 404);

        $overrides = $request->session()->get('manager_repair_status_overrides', []);
        $currentStatus = $overrides[$booking]['repair_status'] ?? $repair['repair_status'];

        if (($currentStatus['key'] ?? null) === 'completed') {
            return redirect()
                ->route('manager.repairs.index', ['repair' => $booking])
                ->with('repair_notice', "{$booking} is already marked completed.");
        }

        // Temporary session persistence keeps the frontend flow testable.
        // Replace this block with BookingStatusManager::markAsCompleted().
        // That backend service must authorize the manager and write the audit log.
        $overrides[$booking] = [
            'repair_status' => ['key' => 'completed', 'label' => 'Completed'],
            'completed_at' => now(),
            'completed_by' => auth()->user()?->name ?? 'Elmer Daniel',
        ];
        $request->session()->put('manager_repair_status_overrides', $overrides);

        return redirect()
            ->route('manager.repairs.index', ['repair' => $booking])
            ->with('repair_notice', "{$booking} was marked completed. The update is ready to be shown to the customer.");
    }

    private function repairRecords(): Collection
    {
        return collect([
            [
                'reference' => 'PDL-1042',
                'customer' => ['name' => 'Paolo Tan', 'initials' => 'PT', 'email' => 'paolo.tan@example.com', 'phone' => '+63 917 000 1042'],
                'service' => 'Brake inspection',
                'appointment_at' => now()->startOfDay()->setTime(11, 30),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'pending', 'label' => 'Pending'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => null,
                'completed_at' => null,
                'completed_by' => null,
            ],
            [
                'reference' => 'PDL-1041',
                'customer' => ['name' => 'Lia Mendoza', 'initials' => 'LM', 'email' => 'lia.mendoza@example.com', 'phone' => '+63 917 000 1041'],
                'service' => 'General tune-up',
                'appointment_at' => now()->subDay()->setTime(9, 0),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'successful', 'label' => 'Successful'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => null,
                'completed_at' => null,
                'completed_by' => null,
            ],
            [
                'reference' => 'PDL-1038',
                'customer' => ['name' => 'Rafi Gomez', 'initials' => 'RG', 'email' => 'rafi.gomez@example.com', 'phone' => '+63 917 000 1038'],
                'service' => 'Drivetrain repair',
                'appointment_at' => now()->subDays(2)->setTime(14, 0),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'successful', 'label' => 'Successful'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => null,
                'completed_at' => null,
                'completed_by' => null,
            ],
            [
                'reference' => 'PDL-1039',
                'customer' => ['name' => 'Mia Flores', 'initials' => 'MF', 'email' => 'mia.flores@example.com', 'phone' => '+63 917 000 1039'],
                'service' => 'General tune-up',
                'appointment_at' => now()->subDays(3)->setTime(11, 0),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'successful', 'label' => 'Successful'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => ['key' => 'completed', 'label' => 'Completed'],
                'completed_at' => now()->subDay()->setTime(16, 20),
                'completed_by' => 'Elmer Daniel',
            ],
            [
                'reference' => 'PDL-1037',
                'customer' => ['name' => 'Noah Ramos', 'initials' => 'NR', 'email' => 'noah.ramos@example.com', 'phone' => '+63 917 000 1037'],
                'service' => 'Brake inspection',
                'appointment_at' => now()->subDays(4)->setTime(10, 30),
                'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
                'payment_status' => ['key' => 'successful', 'label' => 'Successful'],
                'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
                'repair_status' => ['key' => 'completed', 'label' => 'Completed'],
                'completed_at' => now()->startOfDay()->setTime(10, 5),
                'completed_by' => 'Elmer Daniel',
            ],
        ]);
    }

    private function auditEvents(array $repair): Collection
    {
        $events = collect([
            [
                'label' => 'Booking confirmed',
                'detail' => 'The appointment became eligible for workshop scheduling.',
                'at' => $repair['appointment_at']->copy()->subDays(2),
                'tone' => 'confirmed',
            ],
            [
                'label' => 'Appointment scheduled',
                'detail' => $repair['calendar_status']['key'] === 'synchronized'
                    ? 'The confirmed appointment is linked to Google Calendar.'
                    : 'Calendar synchronization requires follow-up.',
                'at' => $repair['appointment_at']->copy()->subDay(),
                'tone' => $repair['calendar_status']['key'],
            ],
        ]);

        if ($repair['completed_at']) {
            $events->push([
                'label' => 'Repair marked completed',
                'detail' => 'Updated by '.($repair['completed_by'] ?? 'an authorized manager').'.',
                'at' => $repair['completed_at'],
                'tone' => 'completed',
            ]);
        }

        return $events->sortByDesc('at')->values();
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
