<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ActivityEvent;
use App\Models\Booking;
use App\Models\Repair;
use App\Services\Manager\ActivityLogger;
use App\Services\Manager\ManagerPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RepairsController extends Controller
{
    private const ACTIVE = ['awaiting_arrival', 'at_shop', 'in_progress'];

    private const RECORDS = ['completed', 'claimed'];

    /**
     * One entry per workshop transition. The rules are unchanged from the
     * previous version: arrive from awaiting_arrival, start from at_shop and
     * only when paid, complete from in_progress, claim from completed.
     */
    private const TRANSITIONS = [
        'arrive' => [
            'from' => 'awaiting_arrival', 'to' => 'at_shop', 'at' => 'arrived_at', 'by' => 'arrived_by',
            'reject' => 'Bike arrival can only be recorded while the repair is awaiting arrival.',
            'accept' => 'The bike was recorded at the shop.',
            'accept_tab' => 'active', 'reject_tab' => 'active', 'requires_payment' => false,
            'event' => 'repair.arrived', 'operation' => 'markAsArrived', 'status_label' => 'Bike at shop',
            'title' => 'Bike arrived at shop', 'summary' => 'The authorized manager recorded the bike at the shop.',
            'detail' => 'The arrival was saved independently from booking, payment, and calendar states.',
        ],
        'start' => [
            'from' => 'at_shop', 'to' => 'in_progress', 'at' => 'started_at', 'by' => 'started_by',
            'reject' => 'The repair can only begin after the bike is recorded at the shop.',
            'accept' => 'Repair work was started.',
            'accept_tab' => 'active', 'reject_tab' => 'active', 'requires_payment' => true,
            'event' => 'repair.started', 'operation' => 'startRepair', 'status_label' => 'Repair in progress',
            'title' => 'Repair work started', 'summary' => 'The authorized manager started the repair after verified payment.',
            'detail' => 'The repair start was saved independently from booking, payment, and calendar states.',
        ],
        'complete' => [
            'from' => 'in_progress', 'to' => 'completed', 'at' => 'completed_at', 'by' => 'completed_by',
            'reject' => 'Only a repair in progress can be marked completed.',
            'accept' => 'The repair was completed and is ready for customer pickup.',
            'accept_tab' => 'records', 'reject_tab' => 'active', 'requires_payment' => false,
            'event' => 'repair.completed', 'operation' => 'markAsCompleted', 'status_label' => 'Completed',
            'title' => 'Repair marked completed', 'summary' => 'The authorized manager recorded the final repair status.',
            'detail' => 'The repair completion update was saved independently from booking, payment, and calendar states.',
        ],
        'claim' => [
            'from' => 'completed', 'to' => 'claimed', 'at' => 'claimed_at', 'by' => 'claimed_by',
            'reject' => 'Only a completed repair can be marked as claimed.',
            'accept' => 'The bike was marked as claimed.',
            'accept_tab' => 'records', 'reject_tab' => 'records', 'requires_payment' => false,
            'event' => 'repair.claimed', 'operation' => 'markAsClaimed', 'status_label' => 'Claimed',
            'title' => 'Bike claimed by customer', 'summary' => 'The authorized manager recorded the customer pickup.',
            'detail' => 'The pickup was saved independently from booking, payment, and calendar states.',
        ],
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tab' => ['nullable', 'in:active,records'],
            'status' => ['nullable', 'in:awaiting_arrival,at_shop,in_progress,completed,claimed'],
            'repair' => ['nullable', 'string', 'max:40'],
        ]);

        $repairs = $this->repairRecords();

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

        $filteredRepairs = $repairs
            ->filter(function (array $repair) use ($tab): bool {
                return in_array($repair['repair_status']['key'], $tab === 'records' ? self::RECORDS : self::ACTIVE, true);
            })
            ->when($filters['search'] !== '', function (Collection $records) use ($filters): Collection {
                $search = Str::lower($filters['search']);

                return $records->filter(fn (array $repair): bool => Str::contains(
                    Str::lower(implode(' ', [
                        $repair['reference'],
                        $repair['customer']['name'],
                        $repair['service'],
                        $repair['notes'] ?? '',
                        $repair['repair_status']['label'],
                    ])),
                    $search,
                ));
            })
            ->when($filters['status'] !== '', fn (Collection $records): Collection => $records->filter(
                fn (array $repair): bool => $repair['repair_status']['key'] === $filters['status'],
            ))
            ->when(
                $tab === 'records',
                fn (Collection $records): Collection => $records->sortByDesc(fn (array $repair): int => ($repair['claimed_at'] ?? $repair['completed_at'])?->getTimestamp() ?? 0),
                fn (Collection $records): Collection => $records->sortBy('appointment_at'),
            )
            ->values();

        $selected = $filteredRepairs->firstWhere('reference', $validated['repair'] ?? null) ?? $filteredRepairs->first();
        $now = now(config('booking.timezone'));

        return view('manager.repairs', [
            'manager' => ManagerPresenter::manager($request->user()),
            'navigation' => ['pending_bookings' => ManagerPresenter::pendingBookings()],
            'summary' => [
                'awaiting_today' => $repairs->filter(fn (array $r): bool => $r['repair_status']['key'] === 'awaiting_arrival' && $r['appointment_at']->isSameDay($now))->count(),
                'at_shop' => $repairs->where('repair_status.key', 'at_shop')->count(),
                'in_progress' => $repairs->where('repair_status.key', 'in_progress')->count(),
                'completed_today' => $repairs->filter(fn (array $r): bool => $r['completed_at']?->setTimezone(config('booking.timezone'))->isSameDay($now) === true)->count(),
            ],
            'tabCounts' => [
                'active' => $repairs->filter(fn (array $repair): bool => in_array($repair['repair_status']['key'], self::ACTIVE, true))->count(),
                'records' => $repairs->filter(fn (array $repair): bool => in_array($repair['repair_status']['key'], self::RECORDS, true))->count(),
            ],
            'statusOptions' => $statusOptions,
            'filters' => $filters,
            'repairs' => $filteredRepairs,
            'selectedRepair' => $selected,
            'auditEvents' => $selected ? $this->auditEvents($selected) : collect(),
        ]);
    }

    public function markArrived(Request $request, string $booking): RedirectResponse
    {
        return $this->transition($request, $booking, 'arrive');
    }

    public function start(Request $request, string $booking): RedirectResponse
    {
        return $this->transition($request, $booking, 'start');
    }

    public function markCompleted(Request $request, string $booking): RedirectResponse
    {
        return $this->transition($request, $booking, 'complete');
    }

    public function markClaimed(Request $request, string $booking): RedirectResponse
    {
        return $this->transition($request, $booking, 'claim');
    }

    private function transition(Request $request, string $reference, string $action): RedirectResponse
    {
        $rule = self::TRANSITIONS[$action];
        $user = $request->user();
        $bookingRecord = Booking::query()->where('reference', $reference)->firstOrFail();

        // The repair row is locked for the whole check-and-update, so two
        // clicks (or two managers) cannot apply the same transition twice.
        $rejection = DB::transaction(function () use ($bookingRecord, $rule, $user): ?string {
            $repair = Repair::query()->where('booking_id', $bookingRecord->id)->lockForUpdate()->first();
            abort_unless($repair, 404);

            if ($repair->status !== $rule['from']) {
                return $rule['reject'];
            }

            $booking = Booking::query()->whereKey($bookingRecord->id)->firstOrFail();

            if ($rule['requires_payment'] && $booking->payment_status !== 'paid') {
                return 'Payment must be successful before repair work can begin.';
            }

            $repair->update([
                'status' => $rule['to'],
                // Stored as UTC: Eloquent writes datetimes without an offset.
                $rule['at'] => now()->setTimezone('UTC'),
                $rule['by'] => $user?->id,
            ]);

            ActivityLogger::record(
                eventKey: $rule['event'],
                category: 'repair',
                result: 'successful',
                title: $rule['title'],
                summary: $rule['summary'],
                detail: $rule['detail'],
                booking: $booking,
                metadata: [
                    'Operation' => $rule['operation'],
                    'Repair status' => $rule['status_label'],
                    'Customer visibility' => 'Available',
                ],
                actor: $user,
                source: 'manager',
            );

            return null;
        });

        return $rejection === null
            ? $this->transitionAccepted($reference, $rule['accept'], $rule['accept_tab'])
            : $this->transitionRejected($reference, $rejection, $rule['reject_tab']);
    }

    private function transitionRejected(string $booking, string $message, string $tab = 'active'): RedirectResponse
    {
        return redirect()
            ->route('manager.repairs.index', ['tab' => $tab, 'repair' => $booking])
            ->with('repair_notice', $message);
    }

    private function transitionAccepted(string $booking, string $message, string $tab = 'active'): RedirectResponse
    {
        return redirect()
            ->route('manager.repairs.index', ['tab' => $tab, 'repair' => $booking])
            ->with('repair_notice', "{$booking}: {$message}");
    }

    /** Every repair of a confirmed (or completed) booking, as view-contract arrays. */
    private function repairRecords(): Collection
    {
        return Repair::query()
            ->with(['booking.service', 'booking.payment', 'arrivedBy', 'startedBy', 'completedBy', 'claimedBy'])
            ->whereHas('booking', fn ($query) => $query->whereIn('booking_status', ['confirmed', 'completed']))
            ->get()
            ->map(fn (Repair $repair): array => ManagerPresenter::repair($repair))
            ->values();
    }

    private function auditEvents(array $repair): Collection
    {
        $booking = Booking::query()->where('reference', $repair['reference'])->first();
        $logged = $booking
            ? ActivityEvent::query()
                ->where('booking_id', $booking->id)
                ->whereIn('event_key', ['booking.confirmed', 'calendar.sync.completed'])
                ->orderBy('occurred_at')
                ->get()
                ->unique('event_key')
            : collect();

        $confirmed = $logged->firstWhere('event_key', 'booking.confirmed');
        $synced = $logged->firstWhere('event_key', 'calendar.sync.completed');
        $events = collect();

        // Auto-confirmed bookings have no manager event, so fall back to the
        // moment the booking was submitted.
        $confirmedAt = ManagerPresenter::shopTime($confirmed?->occurred_at ?? $booking?->created_at);
        if ($confirmedAt) {
            $events->push([
                'label' => 'Booking confirmed',
                'detail' => 'The appointment became eligible for workshop scheduling.',
                'at' => $confirmedAt,
                'tone' => 'confirmed',
            ]);
        }

        if ($synced) {
            $events->push([
                'label' => 'Appointment scheduled',
                'detail' => 'The confirmed appointment is linked to Google Calendar.',
                'at' => ManagerPresenter::shopTime($synced->occurred_at),
                'tone' => 'synchronized',
            ]);
        }

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

        return $events->sortByDesc(fn (array $event): int => $event['at']->getTimestamp())->values();
    }
}
