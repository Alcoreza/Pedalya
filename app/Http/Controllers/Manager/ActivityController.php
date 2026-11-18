<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ActivityEvent;
use App\Services\Manager\ManagerPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /** The stream shows the most recent events only. */
    private const STREAM_LIMIT = 200;

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'in:booking,payment,calendar,repair'],
            'result' => ['nullable', 'in:successful,pending,failed'],
            'activity' => ['nullable', 'string', 'max:40'],
        ]);

        $filters = [
            'search' => trim((string) ($validated['search'] ?? '')),
            'category' => (string) ($validated['category'] ?? ''),
            'result' => (string) ($validated['result'] ?? ''),
        ];

        $query = ActivityEvent::query()->orderByDesc('occurred_at')->orderByDesc('id');

        if ($filters['search'] !== '') {
            // Escape LIKE wildcards so the search is literal.
            $term = '%'.addcslashes($filters['search'], '\\%_').'%';
            $eventId = ManagerPresenter::activityKey(strtoupper($filters['search']));

            $query->where(function ($q) use ($term, $eventId): void {
                $q->where('event_key', 'ilike', $term)
                    ->orWhere('title', 'ilike', $term)
                    ->orWhere('booking_reference', 'ilike', $term)
                    ->orWhere('actor_label', 'ilike', $term)
                    ->orWhere('provider', 'ilike', $term);

                if ($eventId !== null) {
                    $q->orWhere('id', $eventId);
                }
            });
        }

        if ($filters['category'] !== '') {
            $query->where('category', $filters['category']);
        }

        if ($filters['result'] !== '') {
            $query->where('result', $filters['result']);
        }

        $activities = $query->limit(self::STREAM_LIMIT)->get()
            ->map(fn (ActivityEvent $event): array => ManagerPresenter::activity($event))
            ->values();

        // Same fallback order as before: requested event, first filtered, latest overall.
        $selectedActivity = null;
        $requestedId = $validated['activity'] ?? null;

        if ($requestedId !== null) {
            $selectedActivity = $activities->firstWhere('id', $requestedId);

            if (! $selectedActivity && ($key = ManagerPresenter::activityKey($requestedId)) !== null) {
                $event = ActivityEvent::query()->find($key);
                $selectedActivity = $event ? ManagerPresenter::activity($event) : null;
            }
        }

        $selectedActivity ??= $activities->first();

        if (! $selectedActivity) {
            $latest = ActivityEvent::query()->orderByDesc('occurred_at')->orderByDesc('id')->first();
            $selectedActivity = $latest ? ManagerPresenter::activity($latest) : null;
        }

        return view('manager.activity', [
            'manager' => ManagerPresenter::manager($request->user()),
            'navigation' => [
                'pending_bookings' => ManagerPresenter::pendingBookings(),
            ],
            'summary' => $this->summary(),
            'filters' => $filters,
            'filterOptions' => [
                'categories' => ['booking' => 'Booking', 'payment' => 'Payment', 'calendar' => 'Calendar', 'repair' => 'Repair'],
                'results' => ['successful' => 'Successful', 'pending' => 'Pending', 'failed' => 'Failed'],
            ],
            'activities' => $activities,
            'selectedActivity' => $selectedActivity,
        ]);
    }

    /** Overview counts across all events (not just the filtered list), in one query. */
    private function summary(): array
    {
        $now = ManagerPresenter::now();

        $row = ActivityEvent::query()
            ->selectRaw(
                "count(*) filter (where occurred_at >= ? and occurred_at <= ?) as today,
                 count(*) filter (where result = 'successful') as successful,
                 count(*) filter (where result in ('pending', 'failed')) as follow_up,
                 count(*) filter (where source = 'manager') as manager_changes",
                [$now->copy()->startOfDay()->toIso8601String(), $now->copy()->endOfDay()->toIso8601String()],
            )
            ->first();

        return [
            'today' => (int) ($row->today ?? 0),
            'successful' => (int) ($row->successful ?? 0),
            'follow_up' => (int) ($row->follow_up ?? 0),
            'manager_changes' => (int) ($row->manager_changes ?? 0),
        ];
    }
}
