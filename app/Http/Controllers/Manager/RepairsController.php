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
    private const STATUS_LABELS = [
        'awaiting_arrival' => 'Awaiting arrival',
        'at_shop' => 'Bike at shop',
        'in_progress' => 'Repair in progress',
        'completed' => 'Repair completed',
        'claimed' => 'Bike claimed',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tab' => ['nullable', 'in:active,records'],
            'status' => ['nullable', 'in:awaiting_arrival,at_shop,in_progress,completed,claimed'],
            'repair' => ['nullable', 'string', 'max:40'],
        ]);

        $managerName = $this->managerName();
        $overrides = $request->session()->get('manager_repair_status_overrides', []);
        $repairs = $this->repairRecords()->map(function (array $repair) use ($overrides): array {
            return array_replace($repair, $overrides[$repair['reference']] ?? []);
        });

        $tab = (string) ($validated['tab'] ?? 'active');
        $statusOptions = $tab === 'records'
            ? ['completed' => 'Completed', 'claimed' => 'Claimed']
            : ['awaiting_arrival' => 'Awaiting arrival', 'at_shop' => 'At shop', 'in_progress' => 'In progress'];
        $requestedStatus = (string) ($validated['status'] ?? '');

        $filters = [
            'search' => trim((string) ($validated['search'] ?? '')),
            'tab' => $tab,
            'status' => array_key_exists($requestedStatus, $statusOptions) ? $requestedStatus : '',
        ];

        $filteredRepairs = $repairs
            ->filter(function (array $repair) use ($tab): bool {
                $status = $repair['repair_status']['key'];

                return $tab === 'records'
                    ? in_array($status, ['completed', 'claimed'], true)
                    : in_array($status, ['awaiting_arrival', 'at_shop', 'in_progress'], true);
            })
            ->when($filters['search'] !== '', function (Collection $records) use ($filters): Collection {
                $search = Str::lower($filters['search']);

                return $records->filter(fn (array $repair): bool => Str::contains(
                    Str::lower(implode(' ', [
                        $repair['reference'],
                        $repair['customer']['name'],
                        $repair['service'],
                        $repair['repair_status']['label'],
                    ])),
                    $search,
                ));
            })
            ->when($filters['status'] !== '', fn (Collection $records): Collection => $records->filter(
                fn (array $repair): bool => $repair['repair_status']['key'] === $filters['status'],
            ))
            ->values();

        $selectedRepair = $filteredRepairs->firstWhere('reference', $validated['repair'] ?? null)
            ?? $filteredRepairs->first();

        return view('manager.repairs', [
            'manager' => [
                'name' => $managerName,
                'initials' => $this->initials($managerName),
                'role' => 'Manager',
            ],
            'navigation' => ['pending_bookings' => 2],
            'summary' => [
                'awaiting_today' => $repairs->filter(fn (array $repair): bool => $repair['repair_status']['key'] === 'awaiting_arrival' && $repair['appointment_at']->isToday())->count(),
                'at_shop' => $repairs->filter(fn (array $repair): bool => $repair['repair_status']['key'] === 'at_shop')->count(),
                'in_progress' => $repairs->filter(fn (array $repair): bool => $repair['repair_status']['key'] === 'in_progress')->count(),
                'completed_today' => $repairs->filter(fn (array $repair): bool => $repair['completed_at']?->isToday() === true)->count(),
            ],
            'tabCounts' => [
                'active' => $repairs->filter(fn (array $repair): bool => in_array($repair['repair_status']['key'], ['awaiting_arrival', 'at_shop', 'in_progress'], true))->count(),
                'records' => $repairs->filter(fn (array $repair): bool => in_array($repair['repair_status']['key'], ['completed', 'claimed'], true))->count(),
            ],
            'statusOptions' => $statusOptions,
            'filters' => $filters,
            'repairs' => $filteredRepairs,
            'selectedRepair' => $selectedRepair,
            'auditEvents' => $selectedRepair ? $this->auditEvents($selectedRepair) : collect(),
        ]);
    }

    public function markArrived(Request $request, string $booking): RedirectResponse
    {
        $repair = $this->currentRepair($request, $booking);

        if ($repair['repair_status']['key'] !== 'awaiting_arrival') {
            return $this->transitionRejected($booking, 'Bike arrival can only be recorded while the repair is awaiting arrival.');
        }

        $this->storeTransition($request, $booking, [
            'repair_status' => $this->status('at_shop'),
            'arrived_at' => now(),
            'arrived_by' => $this->managerName(),
        ]);

        return $this->transitionAccepted($booking, 'The bike was recorded at the shop.');
    }

    public function start(Request $request, string $booking): RedirectResponse
    {
        $repair = $this->currentRepair($request, $booking);

        if ($repair['repair_status']['key'] !== 'at_shop') {
            return $this->transitionRejected($booking, 'The repair can only begin after the bike is recorded at the shop.');
        }

        if ($repair['payment_status']['key'] !== 'successful') {
            return $this->transitionRejected($booking, 'Payment must be successful before repair work can begin.');
        }

        $this->storeTransition($request, $booking, [
            'repair_status' => $this->status('in_progress'),
            'started_at' => now(),
            'started_by' => $this->managerName(),
        ]);

        return $this->transitionAccepted($booking, 'Repair work was started.');
    }

    public function markCompleted(Request $request, string $booking): RedirectResponse
    {
        $repair = $this->currentRepair($request, $booking);

        if ($repair['repair_status']['key'] !== 'in_progress') {
            return $this->transitionRejected($booking, 'Only a repair in progress can be marked completed.');
        }

        $this->storeTransition($request, $booking, [
            'repair_status' => $this->status('completed'),
            'completed_at' => now(),
            'completed_by' => $this->managerName(),
        ]);

        return $this->transitionAccepted($booking, 'The repair was completed and is ready for customer pickup.', 'records');
    }

    public function markClaimed(Request $request, string $booking): RedirectResponse
    {
        $repair = $this->currentRepair($request, $booking);

        if ($repair['repair_status']['key'] !== 'completed') {
            return $this->transitionRejected($booking, 'Only a completed repair can be marked as claimed.', 'records');
        }

        $this->storeTransition($request, $booking, [
            'repair_status' => $this->status('claimed'),
            'claimed_at' => now(),
            'claimed_by' => $this->managerName(),
        ]);

        return $this->transitionAccepted($booking, 'The bike was marked as claimed.', 'records');
    }

    private function currentRepair(Request $request, string $booking): array
    {
        $repair = $this->repairRecords()->firstWhere('reference', $booking);
        abort_unless($repair, 404);

        return array_replace(
            $repair,
            $request->session()->get("manager_repair_status_overrides.{$booking}", []),
        );
    }

    private function storeTransition(Request $request, string $booking, array $changes): void
    {
        // Temporary session persistence keeps the frontend contract testable.
        // Replace this with the authorized BookingStatusManager and persistent audit log.
        $existing = $request->session()->get("manager_repair_status_overrides.{$booking}", []);
        $request->session()->put("manager_repair_status_overrides.{$booking}", array_replace($existing, $changes));
    }

    private function transitionAccepted(string $booking, string $message, string $tab = 'active'): RedirectResponse
    {
        return redirect()
            ->route('manager.repairs.index', ['tab' => $tab, 'repair' => $booking])
            ->with('repair_notice', "{$booking}: {$message}");
    }

    private function transitionRejected(string $booking, string $message, string $tab = 'active'): RedirectResponse
    {
        return redirect()
            ->route('manager.repairs.index', ['tab' => $tab, 'repair' => $booking])
            ->with('repair_notice', $message);
    }

    private function repairRecords(): Collection
    {
        return collect([
            $this->record('PDL-1052', 'Ana Reyes', 'AR', 'ana.reyes@example.com', '+63 917 000 1052', 'Safety inspection', now()->setTime(15, 0), 'successful', 'awaiting_arrival'),
            $this->record('PDL-1042', 'Paolo Tan', 'PT', 'paolo.tan@example.com', '+63 917 000 1042', 'Brake inspection', now()->setTime(11, 30), 'pending', 'at_shop', ['arrived_at' => now()->setTime(10, 45), 'arrived_by' => 'Elmer Daniel']),
            $this->record('PDL-1051', 'Nico Santos', 'NS', 'nico.santos@example.com', '+63 917 000 1051', 'Wheel alignment', now()->setTime(13, 30), 'successful', 'at_shop', ['arrived_at' => now()->setTime(12, 50), 'arrived_by' => 'Elmer Daniel']),
            $this->record('PDL-1041', 'Lia Mendoza', 'LM', 'lia.mendoza@example.com', '+63 917 000 1041', 'General tune-up', now()->setTime(9, 0), 'successful', 'in_progress', ['arrived_at' => now()->setTime(8, 42), 'arrived_by' => 'Elmer Daniel', 'started_at' => now()->setTime(9, 10), 'started_by' => 'Elmer Daniel']),
            $this->record('PDL-1038', 'Rafi Gomez', 'RG', 'rafi.gomez@example.com', '+63 917 000 1038', 'Drivetrain repair', now()->subDay()->setTime(14, 0), 'successful', 'in_progress', ['arrived_at' => now()->subDay()->setTime(13, 35), 'arrived_by' => 'Elmer Daniel', 'started_at' => now()->subDay()->setTime(14, 15), 'started_by' => 'Elmer Daniel']),
            $this->record('PDL-1039', 'Mia Flores', 'MF', 'mia.flores@example.com', '+63 917 000 1039', 'General tune-up', now()->subDays(2)->setTime(11, 0), 'successful', 'completed', ['arrived_at' => now()->subDays(2)->setTime(10, 40), 'arrived_by' => 'Elmer Daniel', 'started_at' => now()->subDays(2)->setTime(11, 10), 'started_by' => 'Elmer Daniel', 'completed_at' => now()->setTime(10, 5), 'completed_by' => 'Elmer Daniel']),
            $this->record('PDL-1037', 'Noah Ramos', 'NR', 'noah.ramos@example.com', '+63 917 000 1037', 'Brake inspection', now()->subDays(4)->setTime(10, 30), 'successful', 'claimed', ['arrived_at' => now()->subDays(4)->setTime(10, 5), 'arrived_by' => 'Elmer Daniel', 'started_at' => now()->subDays(4)->setTime(10, 40), 'started_by' => 'Elmer Daniel', 'completed_at' => now()->subDays(3)->setTime(15, 20), 'completed_by' => 'Elmer Daniel', 'claimed_at' => now()->subDays(2)->setTime(9, 15), 'claimed_by' => 'Elmer Daniel']),
        ]);
    }

    private function record(string $reference, string $name, string $initials, string $email, string $phone, string $service, mixed $appointmentAt, string $paymentStatus, string $repairStatus, array $events = []): array
    {
        return array_replace([
            'reference' => $reference,
            'customer' => compact('name', 'initials', 'email', 'phone'),
            'service' => $service,
            'appointment_at' => $appointmentAt,
            'booking_status' => ['key' => 'confirmed', 'label' => 'Confirmed'],
            'payment_status' => ['key' => $paymentStatus, 'label' => Str::headline($paymentStatus)],
            'calendar_status' => ['key' => 'synchronized', 'label' => 'Synchronized'],
            'repair_status' => $this->status($repairStatus),
            'arrived_at' => null,
            'arrived_by' => null,
            'started_at' => null,
            'started_by' => null,
            'completed_at' => null,
            'completed_by' => null,
            'claimed_at' => null,
            'claimed_by' => null,
        ], $events);
    }

    private function status(string $key): array
    {
        return ['key' => $key, 'label' => self::STATUS_LABELS[$key]];
    }

    private function auditEvents(array $repair): Collection
    {
        $events = collect([
            ['label' => 'Booking confirmed', 'detail' => 'The appointment became eligible for workshop scheduling.', 'at' => $repair['appointment_at']->copy()->subDays(2), 'tone' => 'confirmed'],
            ['label' => 'Appointment scheduled', 'detail' => 'The confirmed appointment is linked to Google Calendar.', 'at' => $repair['appointment_at']->copy()->subDay(), 'tone' => 'synchronized'],
        ]);

        foreach ([
            ['at' => 'arrived_at', 'by' => 'arrived_by', 'label' => 'Bike arrived at shop', 'tone' => 'at-shop'],
            ['at' => 'started_at', 'by' => 'started_by', 'label' => 'Repair work started', 'tone' => 'in-progress'],
            ['at' => 'completed_at', 'by' => 'completed_by', 'label' => 'Repair marked completed', 'tone' => 'completed'],
            ['at' => 'claimed_at', 'by' => 'claimed_by', 'label' => 'Bike claimed by customer', 'tone' => 'claimed'],
        ] as $event) {
            if ($repair[$event['at']]) {
                $events->push([
                    'label' => $event['label'],
                    'detail' => 'Recorded by '.($repair[$event['by']] ?? 'an authorized manager').'.',
                    'at' => $repair[$event['at']],
                    'tone' => $event['tone'],
                ]);
            }
        }

        return $events->sortByDesc('at')->values();
    }

    private function managerName(): string
    {
        return auth()->user()?->name ?? 'Elmer Daniel';
    }

    private function initials(string $name): string
    {
        return Str::of($name)->explode(' ')->filter()->take(2)->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))->implode('');
    }
}
