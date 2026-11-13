<?php

namespace Tests\Feature;

use App\Services\Bookings\BookingCapacityPolicy;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ManagerBookingsPageTest extends TestCase
{
    public function test_booking_page_separates_automatic_confirmations_from_schedule_reviews(): void
    {
        $response = $this->get(route('manager.bookings.index', ['booking' => 'PDL-1048']));

        $response->assertOk()
            ->assertSee('Auto-confirmed')
            ->assertSee('Schedule review')
            ->assertSee('Requested time window')
            ->assertSee('Bookings within this timeframe')
            ->assertSee('Customer problem details')
            ->assertSee('front brake squeals')
            ->assertDontSee('Slot capacity')
            ->assertSee('PDL-1047')
            ->assertSee('PDL-1046');
    }

    public function test_manager_can_search_booking_problem_details(): void
    {
        $this->get(route('manager.bookings.index', ['search' => 'downhill']))
            ->assertOk()
            ->assertSee('PDL-1048');
    }

    public function test_manager_can_resolve_a_schedule_review(): void
    {
        $response = $this->post(route('manager.bookings.decision', 'PDL-1048'), [
            'decision' => 'confirmed',
        ]);

        $response->assertRedirect(route('manager.bookings.index', ['booking' => 'PDL-1048']));

        $this->get(route('manager.bookings.index', ['booking' => 'PDL-1048']))
            ->assertOk()
            ->assertSee('Manager reviewed')
            ->assertDontSee('Schedule review needed');
    }

    public function test_resolved_booking_cannot_be_decided_twice(): void
    {
        $this->post(route('manager.bookings.decision', 'PDL-1048'), ['decision' => 'rejected']);

        $this->post(route('manager.bookings.decision', 'PDL-1048'), ['decision' => 'confirmed'])
            ->assertStatus(422);
    }

    public function test_capacity_and_service_duration_are_configuration_driven(): void
    {
        config()->set('booking.default_capacity', 2);
        config()->set('booking.services.brake-inspection.duration_minutes', 75);
        config()->set('booking.capacity_overrides', [[
            'days' => [5],
            'start' => '12:00',
            'end' => '15:00',
            'services' => ['brake-inspection'],
            'capacity' => 3,
        ]]);

        $candidate = [
            'reference' => 'PDL-TEST',
            'service' => 'Brake inspection',
            'appointment_at' => Carbon::parse('2026-11-13 13:00'),
            'booking_status' => ['key' => 'review_required'],
        ];

        $assessment = app(BookingCapacityPolicy::class)->assess($candidate, new Collection([$candidate]));

        $this->assertSame(75, $assessment['duration_minutes']);
        $this->assertSame(3, $assessment['capacity']);
        $this->assertSame('14:15', $assessment['ends_at']->format('H:i'));
    }
}
