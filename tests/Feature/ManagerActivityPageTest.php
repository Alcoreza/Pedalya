<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManagerActivityPageTest extends TestCase
{
    public function test_manager_activity_page_displays_operational_event_data(): void
    {
        $response = $this->get(route('manager.activity.index'));

        $response->assertOk()
            ->assertSee('System')
            ->assertSee('activity.')
            ->assertSee('Activity stream')
            ->assertSee('Sensitive values excluded')
            ->assertSee('Booking')
            ->assertSee('Payment')
            ->assertSee('Calendar')
            ->assertSee('Repair')
            ->assertDontSee('%');
    }

    public function test_manager_can_filter_activity_by_category_and_result(): void
    {
        $response = $this->get(route('manager.activity.index', [
            'category' => 'calendar',
            'result' => 'failed',
        ]));

        $response->assertOk()
            ->assertSee('Calendar synchronization failed')
            ->assertDontSee('Payment result verified');
    }
}
