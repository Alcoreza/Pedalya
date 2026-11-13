<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManagerRepairsPageTest extends TestCase
{
    public function test_manager_repairs_page_displays_the_approved_workshop_stages(): void
    {
        $response = $this->get(route('manager.repairs.index'));

        $response
            ->assertOk()
            ->assertSee('Awaiting arrival today')
            ->assertSee('Bike at shop')
            ->assertSee('Repair in progress')
            ->assertSee('Completed today')
            ->assertSee('Active repairs')
            ->assertSee('Completed records')
            ->assertSee('Audit trail')
            ->assertSee('Customer problem details')
            ->assertSee('unstable during sharp turns')
            ->assertDontSee('%');
    }

    public function test_manager_can_search_repair_problem_details(): void
    {
        $this->get(route('manager.repairs.index', ['search' => 'pothole']))
            ->assertOk()
            ->assertSee('PDL-1051')
            ->assertDontSee('PDL-1052');
    }

    public function test_manager_can_move_a_paid_repair_through_the_full_workshop_lifecycle(): void
    {
        $this->post(route('manager.repairs.arrive', 'PDL-1052'))
            ->assertRedirect(route('manager.repairs.index', ['tab' => 'active', 'repair' => 'PDL-1052']))
            ->assertSessionHas('manager_repair_status_overrides.PDL-1052.repair_status.key', 'at_shop')
            ->assertSessionHas('manager_repair_status_overrides.PDL-1052.arrived_by', 'Elmer Daniel');

        $this->post(route('manager.repairs.start', 'PDL-1052'))
            ->assertSessionHas('manager_repair_status_overrides.PDL-1052.repair_status.key', 'in_progress')
            ->assertSessionHas('manager_repair_status_overrides.PDL-1052.started_by', 'Elmer Daniel');

        $this->post(route('manager.repairs.complete', 'PDL-1052'))
            ->assertRedirect(route('manager.repairs.index', ['tab' => 'records', 'repair' => 'PDL-1052']))
            ->assertSessionHas('manager_repair_status_overrides.PDL-1052.repair_status.key', 'completed')
            ->assertSessionHas('manager_repair_status_overrides.PDL-1052.completed_by', 'Elmer Daniel');

        $this->post(route('manager.repairs.claim', 'PDL-1052'))
            ->assertSessionHas('manager_repair_status_overrides.PDL-1052.repair_status.key', 'claimed')
            ->assertSessionHas('manager_repair_status_overrides.PDL-1052.claimed_by', 'Elmer Daniel');
    }

    public function test_repair_cannot_start_until_payment_is_successful(): void
    {
        $response = $this->post(route('manager.repairs.start', 'PDL-1042'));

        $response
            ->assertRedirect(route('manager.repairs.index', ['tab' => 'active', 'repair' => 'PDL-1042']))
            ->assertSessionHas('repair_notice', 'Payment must be successful before repair work can begin.')
            ->assertSessionMissing('manager_repair_status_overrides.PDL-1042.started_at');
    }

    public function test_repair_cannot_be_completed_before_it_is_in_progress(): void
    {
        $response = $this->post(route('manager.repairs.complete', 'PDL-1052'));

        $response
            ->assertSessionHas('repair_notice', 'Only a repair in progress can be marked completed.')
            ->assertSessionMissing('manager_repair_status_overrides.PDL-1052.completed_at');
    }

    public function test_completed_records_tab_contains_completed_and_claimed_repairs(): void
    {
        $response = $this->get(route('manager.repairs.index', ['tab' => 'records']));

        $response
            ->assertOk()
            ->assertSee('Repair completed')
            ->assertSee('Bike claimed')
            ->assertDontSee('PDL-1052');
    }
}
