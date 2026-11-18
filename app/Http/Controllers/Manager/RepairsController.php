<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RepairsController extends Controller
{
    private const LABELS = [
        'awaiting_arrival' => 'Awaiting arrival', 'at_shop' => 'Bike at shop',
        'in_progress' => 'Repair in progress', 'completed' => 'Repair completed', 'claimed' => 'Bike claimed',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tab' => ['nullable', 'in:active,records'],
            'status' => ['nullable', 'in:awaiting_arrival,at_shop,in_progress,completed,claimed'],
            'repair' => ['nullable', 'string', 'max:40'],
        ]);

        $all = Booking::query()
            ->with(['service', 'payment'])
            ->whereIn('booking_status', ['confirmed', 'completed'])
            ->orderByDesc('appointment_at')
            ->get()
            ->map(fn (Booking $booking): array => $this->record($booking));

        $tab = $validated['tab'] ?? 'active';
        $statusOptions = $tab === 'records'
            ? ['completed' => 'Completed', 'claimed' => 'Claimed']
            : ['awaiting_arrival' => 'Awaiting arrival', 'at_shop' => 'At shop', 'in_progress' => 'In progress'];
        $requestedStatus = (string) ($validated['status'] ?? '');
        $filters = [
            'search' => trim((string) ($validated['search'] ?? '')),
            'tab' => $tab,
            'status' => array_key_exists($requestedStatus, $statusOptions) ? $requestedStatus : '',
        ];

        $filtered = $all
            ->filter(fn (array $repair): bool => $tab === 'records'
                ? in_array($repair['repair_status']['key'], ['completed', 'claimed'], true)
                : in_array($repair['repair_status']['key'], ['awaiting_arrival', 'at_shop', 'in_progress'], true))
            ->when($filters['search'] !== '', function (Collection $items) use ($filters): Collection {
                $needle = Str::lower($filters['search']);
                return $items->filter(fn (array $repair): bool => Str::contains(Str::lower(implode(' ', [
                    $repair['reference'], $repair['customer']['name'], $repair['service'], $repair['notes'] ?? '', $repair['repair_status']['label'],
                ])), $needle));
            })
            ->when($filters['status'] !== '', fn (Collection $items): Collection => $items->where('repair_status.key', $filters['status']))
            ->values();

        $selected = $filtered->firstWhere('reference', $validated['repair'] ?? null) ?? $filtered->first();
        $now = now(config('booking.timezone'));

        return view('manager.repairs', [
            'manager' => ['name' => $request->user()->name, 'initials' => $this->initials($request->user()->name), 'role' => 'Manager'],
            'navigation' => ['pending_bookings' => Booking::query()->where('booking_status', 'pending')->where('processing_method', 'review_required')->count()],
            'summary' => [
                'awaiting_today' => $all->filter(fn (array $r): bool => $r['repair_status']['key'] === 'awaiting_arrival' && $r['appointment_at']->isSameDay($now))->count(),
                'at_shop' => $all->where('repair_status.key', 'at_shop')->count(),
                'in_progress' => $all->where('repair_status.key', 'in_progress')->count(),
                'completed_today' => $all->filter(fn (array $r): bool => $r['completed_at']?->setTimezone(config('booking.timezone'))->isSameDay($now) === true)->count(),
            ],
            'tabCounts' => [
                'active' => $all->whereIn('repair_status.key', ['awaiting_arrival', 'at_shop', 'in_progress'])->count(),
                'records' => $all->whereIn('repair_status.key', ['completed', 'claimed'])->count(),
            ],
            'statusOptions' => $statusOptions,
            'filters' => $filters,
            'repairs' => $filtered,
            'selectedRepair' => $selected,
            'auditEvents' => $selected ? $this->auditEvents($selected) : collect(),
        ]);
    }

    public function markArrived(Request $request, string $booking): RedirectResponse
    {
        $record = $this->findRepair($booking);
        abort_unless($this->key($record) === 'awaiting_arrival', 422, 'Bike arrival can only be recorded while the repair is awaiting arrival.');
        $record->update(['repair_status' => 'at_shop', 'repair_arrived_at' => now(), 'repair_arrived_by' => $request->user()->name]);
        return $this->done($booking, 'The bike was recorded at the shop.');
    }

    public function start(Request $request, string $booking): RedirectResponse
    {
        $record = $this->findRepair($booking);
        abort_unless($this->key($record) === 'at_shop', 422, 'The repair can only begin after the bike is recorded at the shop.');
        abort_unless($record->payment_status === 'paid', 422, 'Payment must be successful before repair work can begin.');
        $record->update(['repair_status' => 'in_progress', 'repair_started_at' => now(), 'repair_started_by' => $request->user()->name]);
        return $this->done($booking, 'Repair work was started.');
    }

    public function markCompleted(Request $request, string $booking): RedirectResponse
    {
        $record = $this->findRepair($booking);
        abort_unless($this->key($record) === 'in_progress', 422, 'Only a repair in progress can be marked completed.');
        DB::transaction(function () use ($record, $request): void {
            $record->update([
                'repair_status' => 'completed',
                'repair_completed_at' => now(),
                'repair_completed_by' => $request->user()->name,
                'booking_status' => 'completed',
            ]);
        });
        return $this->done($booking, 'The repair was completed and is ready for customer pickup.', 'records');
    }

    public function markClaimed(Request $request, string $booking): RedirectResponse
    {
        $record = $this->findRepair($booking);
        abort_unless($this->key($record) === 'completed', 422, 'Only a completed repair can be marked as claimed.');
        $record->update(['repair_status' => 'claimed', 'repair_claimed_at' => now(), 'repair_claimed_by' => $request->user()->name]);
        return $this->done($booking, 'The bike was marked as claimed.', 'records');
    }

    private function findRepair(string $reference): Booking
    {
        return Booking::query()->with('payment')->where('reference', $reference)
            ->whereIn('booking_status', ['confirmed', 'completed'])->firstOrFail();
    }

    private function key(Booking $booking): string
    {
        return $booking->repair_status ?? 'awaiting_arrival';
    }

    private function record(Booking $booking): array
    {
        $key = $this->key($booking);
        $repairLabels = self::LABELS;
        $status = ['key' => $key, 'label' => $repairLabels[$key] ?? 'Awaiting arrival'];
        $customerName = $booking->customer_name;
        $paymentKey = $booking->payment_status === 'paid' ? 'successful' : $booking->payment_status;
        $at = $booking->appointment_at->setTimezone(config('booking.timezone'));

        return [
            'id' => $booking->id,
            'reference' => $booking->reference,
            'created_at' => $booking->created_at?->setTimezone(config('booking.timezone')),
            'customer' => ['name' => $customerName, 'initials' => $this->initials($customerName), 'email' => $booking->customer_email, 'phone' => $booking->customer_mobile_number],
            'service' => $booking->service?->name ?? 'Repair service',
            'notes' => $booking->bike_notes,
            'appointment_at' => $at,
            'booking_status' => ['key' => $booking->booking_status, 'label' => Str::headline($booking->booking_status)],
            'payment_status' => ['key' => $paymentKey, 'label' => $paymentKey === 'successful' ? 'Successful' : Str::headline($paymentKey)],
            'calendar_status' => ['key' => $booking->calendar_status ?? 'not_applicable', 'label' => Str::headline(str_replace('_', ' ', $booking->calendar_status ?? 'not_applicable'))],
            'repair_status' => $status,
            'arrived_at' => $booking->repair_arrived_at?->setTimezone(config('booking.timezone')),
            'arrived_by' => $booking->repair_arrived_by,
            'started_at' => $booking->repair_started_at?->setTimezone(config('booking.timezone')),
            'started_by' => $booking->repair_started_by,
            'completed_at' => $booking->repair_completed_at?->setTimezone(config('booking.timezone')),
            'completed_by' => $booking->repair_completed_by,
            'claimed_at' => $booking->repair_claimed_at?->setTimezone(config('booking.timezone')),
            'claimed_by' => $booking->repair_claimed_by,
        ];
    }

    private function auditEvents(array $repair): Collection
    {
        $events = collect([
            ['label' => 'Booking received', 'detail' => 'Appointment request saved in Pedalya.', 'at' => $repair['created_at'], 'tone' => 'confirmed'],
        ]);
        foreach ([
            ['at' => 'arrived_at', 'by' => 'arrived_by', 'label' => 'Bike arrived at shop', 'tone' => 'at-shop'],
            ['at' => 'started_at', 'by' => 'started_by', 'label' => 'Repair work started', 'tone' => 'in-progress'],
            ['at' => 'completed_at', 'by' => 'completed_by', 'label' => 'Repair marked completed', 'tone' => 'completed'],
            ['at' => 'claimed_at', 'by' => 'claimed_by', 'label' => 'Bike claimed by customer', 'tone' => 'claimed'],
        ] as $event) {
            if ($repair[$event['at']]) {
                $events->push(['label' => $event['label'], 'detail' => 'Recorded by '.($repair[$event['by']] ?? 'a manager').'.', 'at' => $repair[$event['at']], 'tone' => $event['tone']]);
            }
        }
        return $events->sortByDesc('at')->values();
    }

    private function done(string $reference, string $message, string $tab = 'active'): RedirectResponse
    {
        return redirect()->route('manager.repairs.index', ['tab' => $tab, 'repair' => $reference])
            ->with('repair_notice', "{$reference}: {$message}");
    }

    private function initials(string $name): string
    {
        return Str::of($name)->explode(' ')->filter()->take(2)->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))->implode('');
    }
}
