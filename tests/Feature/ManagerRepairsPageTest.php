<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManagerRepairsPageTest extends TestCase
{
    public function test_manager_repairs_page_uses_discrete_statuses_without_percentages(): void
    {
        $response = $this->get(route('manager.repairs.index'));

        $response
            ->assertOk()
            ->assertSee('Repair')
            ->assertSee('ledger.')
            ->assertSee('Status not recorded')
            ->assertSee('Completed')
            ->assertSee('Audit trail')
            ->assertDontSee('%');
    }

    public function test_manager_can_mark_a_repair_completed_through_the_backend_contract(): void
    {
        $response = $this->post(route('manager.repairs.complete', 'PDL-1042'));

        $response
            ->assertRedirect(route('manager.repairs.index', ['repair' => 'PDL-1042']))
            ->assertSessionHas('manager_repair_status_overrides.PDL-1042.repair_status.key', 'completed')
            ->assertSessionHas('manager_repair_status_overrides.PDL-1042.completed_by', 'Elmer Daniel');
    }
}
