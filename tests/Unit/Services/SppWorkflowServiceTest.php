<?php

namespace Tests\Unit\Services;

use App\Services\SppWorkflowService;
use Exception;
use Tests\TestCase;

class SppWorkflowServiceTest extends TestCase
{
    protected SppWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SppWorkflowService::class);
    }

    // ==========================================
    // Test Group 1: Initial Position (8 tests)
    // ==========================================

    /** @test */
    public function it_determines_initial_position_for_project_38_area_flow()
    {
        $position = $this->service->determineInitialPosition('38');
        $this->assertEquals('AREA_MANAGER', $position);
    }

    /** @test */
    public function it_determines_initial_position_for_project_40_area_flow()
    {
        $position = $this->service->determineInitialPosition('40');
        $this->assertEquals('AREA_MANAGER', $position);
    }

    /** @test */
    public function it_determines_initial_position_for_project_01_koordinator_keuangan()
    {
        $position = $this->service->determineInitialPosition('01');
        $this->assertEquals('KOORDINATOR_KEUANGAN', $position);
    }

    /** @test */
    public function it_determines_initial_position_for_project_03_koordinator_pk()
    {
        $position = $this->service->determineInitialPosition('03');
        $this->assertEquals('KOORDINATOR_PK', $position);
    }

    /** @test */
    public function it_determines_initial_position_for_project_07_koordinator_tc()
    {
        $position = $this->service->determineInitialPosition('07');
        $this->assertEquals('KOORDINATOR_TC', $position);
    }

    /** @test */
    public function it_determines_initial_position_for_project_02_koordinator_diklat()
    {
        $position = $this->service->determineInitialPosition('02');
        $this->assertEquals('KOORDINATOR_DIKLAT', $position);
    }

    /** @test */
    public function it_determines_initial_position_for_project_04_koordinator_klinik()
    {
        $position = $this->service->determineInitialPosition('04');
        $this->assertEquals('KOORDINATOR_KLINIK', $position);
    }

    /** @test */
    public function it_determines_initial_position_for_project_06_koordinator_batra()
    {
        $position = $this->service->determineInitialPosition('06');
        $this->assertEquals('KOORDINATOR_BATRA', $position);
    }

    /** @test */
    public function it_returns_default_position_for_unmapped_project()
    {
        $position = $this->service->determineInitialPosition('99');
        $this->assertEquals('MANAGER_KEUANGAN', $position);
    }

    // ==========================================
    // Test Group 2: Workflow Transitions (10 tests)
    // ==========================================

    /** @test */
    public function it_routes_area_manager_to_finance_project()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'AREA_MANAGER',
            kodeProject: '38',
            nominal: '30000000',
            action: 'approve'
        );

        $this->assertEquals('FINANCE_PROJECT', $result['next']);
        $this->assertEquals('Pending', $result['status']);
    }

    /** @test */
    public function it_routes_finance_project_to_project_manager()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'FINANCE_PROJECT',
            kodeProject: '38',
            nominal: '30000000',
            action: 'approve'
        );

        $this->assertEquals('PROJECT_MANAGER', $result['next']);
        $this->assertEquals('Pending', $result['status']);
    }

    /** @test */
    public function it_routes_project_manager_to_manager_keuangan()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'PROJECT_MANAGER',
            kodeProject: '38',
            nominal: '30000000',
            action: 'approve'
        );

        $this->assertEquals('MANAGER_KEUANGAN', $result['next']);
        $this->assertEquals('Pending', $result['status']);
    }

    /** @test */
    public function it_routes_manager_keuangan_to_kasir_when_under_50m()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'MANAGER_KEUANGAN',
            kodeProject: '38',
            nominal: '30000000',
            action: 'approve'
        );

        $this->assertEquals('KASIR_PUSAT', $result['next']);
        $this->assertEquals('Approved', $result['status']);
    }

    /** @test */
    public function it_routes_manager_keuangan_to_direktur_when_over_50m()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'MANAGER_KEUANGAN',
            kodeProject: '38',
            nominal: '75000000',
            action: 'approve'
        );

        $this->assertEquals('DIREKTUR', $result['next']);
        $this->assertEquals('Pending Director Otorisasi', $result['status']);
    }

    /** @test */
    public function it_routes_manager_keuangan_to_kasir_when_exactly_50m()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'MANAGER_KEUANGAN',
            kodeProject: '38',
            nominal: '50000000',
            action: 'approve'
        );

        $this->assertEquals('KASIR_PUSAT', $result['next']);
        $this->assertEquals('Approved', $result['status']);
    }

    /** @test */
    public function it_routes_direktur_to_kasir_pusat()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'DIREKTUR',
            kodeProject: '38',
            nominal: '75000000',
            action: 'approve'
        );

        $this->assertEquals('KASIR_PUSAT', $result['next']);
        $this->assertEquals('Approved', $result['status']);
    }

    /** @test */
    public function it_handles_koordinator_keuangan_flow()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'KOORDINATOR_KEUANGAN',
            kodeProject: '01',
            nominal: '20000000',
            action: 'approve'
        );

        $this->assertEquals('MANAGER_KEUANGAN', $result['next']);
        $this->assertEquals('Pending', $result['status']);
    }

    /** @test */
    public function it_handles_koordinator_batra_flow()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'KOORDINATOR_BATRA',
            kodeProject: '06',
            nominal: '15000000',
            action: 'approve'
        );

        $this->assertEquals('MANAGER_PKP', $result['next']);
        $this->assertEquals('Pending', $result['status']);
    }

    /** @test */
    public function it_handles_manager_pkp_flow()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'MANAGER_PKP',
            kodeProject: '06',
            nominal: '15000000',
            action: 'approve'
        );

        $this->assertEquals('MANAGER_KEUANGAN', $result['next']);
        $this->assertEquals('Pending', $result['status']);
    }

    // ==========================================
    // Test Group 3: Common Actions (2 tests)
    // ==========================================

    /** @test */
    public function it_routes_to_maker_on_revise_action()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'MANAGER_KEUANGAN',
            kodeProject: '38',
            nominal: '30000000',
            action: 'revise'
        );

        $this->assertEquals('MAKER', $result['next']);
        $this->assertEquals('Revisi', $result['status']);
    }

    /** @test */
    public function it_routes_to_rejected_status_on_reject_action()
    {
        $result = $this->service->getNextPosition(
            currentRole: 'MANAGER_KEUANGAN',
            kodeProject: '38',
            nominal: '30000000',
            action: 'reject'
        );

        $this->assertEquals('REJECTED', $result['next']);
        $this->assertEquals('Rejected', $result['status']);
    }

    // ==========================================
    // Test Group 4: Validation (3 tests)
    // ==========================================

    /** @test */
    public function it_validates_workflow_transition_success()
    {
        $surat = (object) [
            'no_surat' => 'TEST-001',
            'posisi_saat_ini' => 'MANAGER_KEUANGAN',
            'status_surat' => 'Pending',
        ];

        $isValid = $this->service->validateWorkflowTransition($surat, 'MANAGER_KEUANGAN');
        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_validates_workflow_transition_failure()
    {
        $surat = (object) [
            'no_surat' => 'TEST-001',
            'posisi_saat_ini' => 'DIREKTUR',
            'status_surat' => 'Pending Director Otorisasi',
        ];

        $isValid = $this->service->validateWorkflowTransition($surat, 'MANAGER_KEUANGAN');
        $this->assertFalse($isValid);
    }

    /** @test */
    public function it_disallows_admin_to_act_on_any_workflow()
    {
        $surat = (object) [
            'no_surat' => 'TEST-001',
            'posisi_saat_ini' => 'DIREKTUR',
            'status_surat' => 'Pending',
        ];

        $isValid = $this->service->validateWorkflowTransition($surat, 'ADMIN');
        $this->assertFalse($isValid);
    }

    // ==========================================
    // Test Group 5: Edge Cases (4 tests)
    // ==========================================

    /** @test */
    public function it_throws_exception_for_unmapped_role()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('WORKFLOW UNMAPPED');

        $this->service->getNextPosition(
            currentRole: 'INVALID_ROLE',
            kodeProject: '38',
            nominal: '30000000',
            action: 'approve'
        );
    }

    /** @test */
    public function it_gets_workflow_roles_for_project()
    {
        $roles = $this->service->getWorkflowRoles('38');

        $this->assertIsArray($roles);
        $this->assertContains('AREA_MANAGER', $roles);
        $this->assertContains('FINANCE_PROJECT', $roles);
        $this->assertContains('PROJECT_MANAGER', $roles);
        $this->assertContains('MANAGER_KEUANGAN', $roles);
        $this->assertContains('DIREKTUR', $roles);
    }

    /** @test */
    public function it_checks_if_role_is_in_workflow()
    {
        $this->assertTrue($this->service->isRoleInWorkflow('AREA_MANAGER', '38'));
        $this->assertFalse($this->service->isRoleInWorkflow('KOORDINATOR_KEUANGAN', '38'));
    }

    /** @test */
    public function it_checks_director_approval_requirement()
    {
        $this->assertTrue($this->service->requiresDirectorApproval('75000000'));
        $this->assertFalse($this->service->requiresDirectorApproval('30000000'));
        $this->assertFalse($this->service->requiresDirectorApproval('50000000'));
    }

    /** @test */
    public function it_gets_approval_threshold()
    {
        $threshold = $this->service->getApprovalThreshold();
        $this->assertEquals('50000000', $threshold);
    }
}
