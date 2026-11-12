<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManagerSchedulePageTest extends TestCase
{
    public function test_manager_schedule_page_exposes_separate_operational_statuses(): void
    {
        $response = $this->get(route('manager.schedule.index'));

        $response
            ->assertOk()
            ->assertSee('Seven-day view')
            ->assertSee('Calendar synchronized')
            ->assertSee('Payment')
            ->assertSee('Repair')
            ->assertSee('2 bookings')
            ->assertDontSee('%');
    }

    public function test_schedule_groups_multiple_bookings_in_the_same_date_and_time_slot(): void
    {
        $response = $this->get(route('manager.schedule.index'));

        $response
            ->assertOk()
            ->assertSeeInOrder([
                '11:30 AM',
                '2 bookings',
                'PDL-1042',
                'PDL-1053',
            ])
            ->assertSee('PDL-1054');
    }

    public function test_calendar_retry_keeps_the_booking_decision_separate(): void
    {
        $date = now()->toDateString();

        $response = $this->post(route('manager.schedule.calendar-sync.retry', 'PDL-1045'), [
            'date' => $date,
        ]);

        $response
            ->assertRedirect(route('manager.schedule.index', ['date' => $date, 'booking' => 'PDL-1045']))
            ->assertSessionHas('manager_calendar_sync_overrides.PDL-1045.calendar_status.key', 'pending')
            ->assertSessionHas('manager_calendar_sync_overrides.PDL-1045.sync_attempts', 2);
    }
}
