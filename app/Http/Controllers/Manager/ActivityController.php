<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'in:booking,payment,calendar,repair'],
            'result' => ['nullable', 'in:successful,pending,failed'],
            'activity' => ['nullable', 'string', 'max:40'],
        ]);

        $managerName = auth()->user()?->name ?? 'Elmer Daniel';
        $activities = $this->activityRecords()->sortByDesc('occurred_at')->values();
        $filters = [
            'search' => trim((string) ($validated['search'] ?? '')),
            'category' => (string) ($validated['category'] ?? ''),
            'result' => (string) ($validated['result'] ?? ''),
        ];

        $filteredActivities = $activities
            ->when($filters['search'] !== '', function (Collection $records) use ($filters): Collection {
                $search = Str::lower($filters['search']);

                return $records->filter(fn (array $activity): bool => Str::contains(
                    Str::lower(implode(' ', [
                        $activity['id'],
                        $activity['booking_reference'],
                        $activity['title'],
                        $activity['actor'],
                        $activity['provider'],
                    ])),
                    $search,
                ));
            })
            ->when($filters['category'] !== '', fn (Collection $records): Collection => $records->where('category.key', $filters['category']))
            ->when($filters['result'] !== '', fn (Collection $records): Collection => $records->where('result.key', $filters['result']))
            ->values();

        $selectedId = $validated['activity'] ?? null;
        $selectedActivity = $activities->firstWhere('id', $selectedId)
            ?? $filteredActivities->first()
            ?? $activities->first();

        return view('manager.activity', [
            'manager' => [
                'name' => $managerName,
                'initials' => $this->initials($managerName),
                'role' => 'Manager',
            ],
            'navigation' => [
                'pending_bookings' => 2,
            ],
            'summary' => [
                'today' => $activities->filter(fn (array $activity): bool => $activity['occurred_at']->isToday())->count(),
                'successful' => $activities->where('result.key', 'successful')->count(),
                'follow_up' => $activities->whereIn('result.key', ['pending', 'failed'])->count(),
                'manager_changes' => $activities->where('source', 'manager')->count(),
            ],
            'filters' => $filters,
            'filterOptions' => [
                'categories' => ['booking' => 'Booking', 'payment' => 'Payment', 'calendar' => 'Calendar', 'repair' => 'Repair'],
                'results' => ['successful' => 'Successful', 'pending' => 'Pending', 'failed' => 'Failed'],
            ],
            'activities' => $filteredActivities,
            'selectedActivity' => $selectedActivity,
        ]);
    }

    private function activityRecords(): Collection
    {
        return collect([
            [
                'id' => 'EVT-2048',
                'event_key' => 'repair.completed',
                'category' => ['key' => 'repair', 'label' => 'Repair'],
                'result' => ['key' => 'successful', 'label' => 'Successful'],
                'title' => 'Repair marked completed',
                'summary' => 'The authorized manager recorded the final repair status.',
                'detail' => 'The repair completion update was saved independently from booking, payment, and calendar states.',
                'booking_reference' => 'PDL-1037',
                'provider' => 'Pedalya',
                'provider_reference' => null,
                'actor' => 'Elmer Daniel',
                'source' => 'manager',
                'occurred_at' => now()->subMinutes(18),
                'metadata' => [
                    ['label' => 'Operation', 'value' => 'markAsCompleted'],
                    ['label' => 'Repair status', 'value' => 'Completed'],
                    ['label' => 'Customer visibility', 'value' => 'Available'],
                ],
            ],
            [
                'id' => 'EVT-2047',
                'event_key' => 'calendar.sync.failed',
                'category' => ['key' => 'calendar', 'label' => 'Calendar'],
                'result' => ['key' => 'failed', 'label' => 'Failed'],
                'title' => 'Calendar synchronization failed',
                'summary' => 'The confirmed booking remains valid and needs calendar follow-up.',
                'detail' => 'The provider did not complete the event creation request. No calendar event ID was stored, and the booking confirmation was not reversed.',
                'booking_reference' => 'PDL-1045',
                'provider' => 'Google Calendar',
                'provider_reference' => null,
                'actor' => 'System process',
                'source' => 'system',
                'occurred_at' => now()->subMinutes(42),
                'metadata' => [
                    ['label' => 'Attempt', 'value' => '1 of 3'],
                    ['label' => 'Error type', 'value' => 'Temporary provider error'],
                    ['label' => 'Next action', 'value' => 'Safe retry'],
                ],
            ],
            [
                'id' => 'EVT-2046',
                'event_key' => 'payment.result.verified',
                'category' => ['key' => 'payment', 'label' => 'Payment'],
                'result' => ['key' => 'successful', 'label' => 'Successful'],
                'title' => 'Payment result verified',
                'summary' => 'Trusted provider references matched the stored booking payment record.',
                'detail' => 'The successful result was verified server-side before the payment status changed. Duplicate processing checks were applied.',
                'booking_reference' => 'PDL-1046',
                'provider' => 'PayMongo',
                'provider_reference' => 'pay_test_1046',
                'actor' => 'System process',
                'source' => 'system',
                'occurred_at' => now()->subHour()->subMinutes(12),
                'metadata' => [
                    ['label' => 'Payment status', 'value' => 'Successful'],
                    ['label' => 'Verification', 'value' => 'Matched'],
                    ['label' => 'Duplicate result', 'value' => 'No'],
                ],
            ],
            [
                'id' => 'EVT-2045',
                'event_key' => 'booking.confirmed',
                'category' => ['key' => 'booking', 'label' => 'Booking'],
                'result' => ['key' => 'successful', 'label' => 'Successful'],
                'title' => 'Booking request confirmed',
                'summary' => 'The manager accepted the customer booking request.',
                'detail' => 'The booking decision was saved first. Calendar synchronization was queued as a separate operation.',
                'booking_reference' => 'PDL-1046',
                'provider' => 'Pedalya',
                'provider_reference' => null,
                'actor' => 'Elmer Daniel',
                'source' => 'manager',
                'occurred_at' => now()->subHours(2),
                'metadata' => [
                    ['label' => 'Previous status', 'value' => 'Pending review'],
                    ['label' => 'Booking status', 'value' => 'Confirmed'],
                    ['label' => 'Calendar action', 'value' => 'Queued'],
                ],
            ],
            [
                'id' => 'EVT-2044',
                'event_key' => 'calendar.sync.completed',
                'category' => ['key' => 'calendar', 'label' => 'Calendar'],
                'result' => ['key' => 'successful', 'label' => 'Successful'],
                'title' => 'Calendar event synchronized',
                'summary' => 'The confirmed appointment was linked to the shop calendar.',
                'detail' => 'The provider event ID was stored against the booking after successful event creation.',
                'booking_reference' => 'PDL-1042',
                'provider' => 'Google Calendar',
                'provider_reference' => 'gcal_pdl_1042',
                'actor' => 'System process',
                'source' => 'system',
                'occurred_at' => now()->subHours(3)->subMinutes(8),
                'metadata' => [
                    ['label' => 'Attempt', 'value' => '1 of 3'],
                    ['label' => 'Calendar status', 'value' => 'Synchronized'],
                    ['label' => 'Event ID stored', 'value' => 'Yes'],
                ],
            ],
            [
                'id' => 'EVT-2043',
                'event_key' => 'calendar.retry.queued',
                'category' => ['key' => 'calendar', 'label' => 'Calendar'],
                'result' => ['key' => 'pending', 'label' => 'Pending'],
                'title' => 'Calendar retry queued',
                'summary' => 'A safe retry was requested for a confirmed booking.',
                'detail' => 'The synchronization service must check whether the event already exists before creating another event.',
                'booking_reference' => 'PDL-1045',
                'provider' => 'Google Calendar',
                'provider_reference' => null,
                'actor' => 'Elmer Daniel',
                'source' => 'manager',
                'occurred_at' => now()->subHours(4),
                'metadata' => [
                    ['label' => 'Attempt', 'value' => '2 of 3'],
                    ['label' => 'Duplicate check', 'value' => 'Required'],
                    ['label' => 'Booking status', 'value' => 'Confirmed'],
                ],
            ],
            [
                'id' => 'EVT-2042',
                'event_key' => 'payment.result.failed',
                'category' => ['key' => 'payment', 'label' => 'Payment'],
                'result' => ['key' => 'failed', 'label' => 'Failed'],
                'title' => 'Payment attempt unsuccessful',
                'summary' => 'The unsuccessful provider result did not mark the booking paid.',
                'detail' => 'The payment remains unsuccessful. A new attempt may only begin when initiated through the approved customer flow.',
                'booking_reference' => 'PDL-1044',
                'provider' => 'PayMongo',
                'provider_reference' => 'pay_test_1044',
                'actor' => 'System process',
                'source' => 'system',
                'occurred_at' => now()->subDay()->setTime(16, 40),
                'metadata' => [
                    ['label' => 'Payment status', 'value' => 'Failed'],
                    ['label' => 'Booking marked paid', 'value' => 'No'],
                    ['label' => 'Automatic charge retry', 'value' => 'No'],
                ],
            ],
            [
                'id' => 'EVT-2041',
                'event_key' => 'booking.rejected',
                'category' => ['key' => 'booking', 'label' => 'Booking'],
                'result' => ['key' => 'successful', 'label' => 'Successful'],
                'title' => 'Booking request rejected',
                'summary' => 'The manager rejected the request without creating a calendar event.',
                'detail' => 'The booking status changed to rejected. Calendar synchronization remained not applicable.',
                'booking_reference' => 'PDL-1044',
                'provider' => 'Pedalya',
                'provider_reference' => null,
                'actor' => 'Elmer Daniel',
                'source' => 'manager',
                'occurred_at' => now()->subDay()->setTime(15, 15),
                'metadata' => [
                    ['label' => 'Previous status', 'value' => 'Pending review'],
                    ['label' => 'Booking status', 'value' => 'Rejected'],
                    ['label' => 'Calendar event created', 'value' => 'No'],
                ],
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
