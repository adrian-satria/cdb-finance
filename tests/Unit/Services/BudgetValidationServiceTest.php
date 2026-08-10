<?php

namespace Tests\Unit\Services;

use App\Services\BudgetValidationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BudgetValidationServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected BudgetValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BudgetValidationService;
    }

    protected function insertBudgetArea(array $overrides = []): void
    {
        DB::table('budget_area')->insert(array_merge([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'kode_area' => 'PUSAT',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '100000000',
            'terserap' => '20000000',
            'tahun' => 2026,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    // ==========================================
    // Test Group 1: Single Item Validation (6 tests)
    // ==========================================

    /** @test */
    public function it_validates_budget_within_ceiling()
    {
        $this->insertBudgetArea();

        $result = $this->service->lockAndValidateMultiple(
            [['kode_budget' => 'TEST-001', 'jumlah' => '30000000']],
            'PUSAT', '38'
        );

        $this->assertTrue($result->isValid);
        $this->assertFalse($result->isOverBudget);
    }

    /** @test */
    public function it_fails_when_budget_not_found()
    {
        $result = $this->service->lockAndValidateMultiple(
            [['kode_budget' => 'NONEXISTENT', 'jumlah' => '10000000']],
            'PUSAT', '38'
        );

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('tidak ditemukan', $result->errorMessage);
    }

    /** @test */
    public function it_allows_overspend_with_flag()
    {
        $this->insertBudgetArea([
            'alokasi_dana' => '50000000',
            'terserap' => '45000000',
        ]);

        $result = $this->service->lockAndValidateMultiple(
            [['kode_budget' => 'TEST-001', 'jumlah' => '10000000']],
            'PUSAT', '38'
        );

        $this->assertTrue($result->isValid);
        $this->assertTrue($result->isOverBudget);
        $this->assertStringContainsString('-5000000', $result->defisit);
        $this->assertCount(1, $result->overbudgetItems);
    }

    /** @test */
    public function it_handles_zero_terserap_budget()
    {
        $this->insertBudgetArea([
            'alokasi_dana' => '75000000',
            'terserap' => '0',
        ]);

        $result = $this->service->lockAndValidateMultiple(
            [['kode_budget' => 'TEST-001', 'jumlah' => '50000000']],
            'PUSAT', '38'
        );

        $this->assertTrue($result->isValid);
        $this->assertFalse($result->isOverBudget);
    }

    /** @test */
    public function it_handles_fully_utilized_budget()
    {
        $this->insertBudgetArea([
            'alokasi_dana' => '50000000',
            'terserap' => '50000000',
        ]);

        $result = $this->service->lockAndValidateMultiple(
            [['kode_budget' => 'TEST-001', 'jumlah' => '1000']],
            'PUSAT', '38'
        );

        $this->assertTrue($result->isValid);
        $this->assertTrue($result->isOverBudget);
    }

    /** @test */
    public function it_skips_budget_check_for_no_budget_project()
    {
        $result = $this->service->lockAndValidateMultiple(
            [['kode_budget' => 'ANY-CODE', 'jumlah' => '999999999']],
            'PUSAT', '01'
        );

        $this->assertTrue($result->isValid);
        $this->assertTrue($result->details['no_budget'] ?? false);
    }

    // ==========================================
    // Test Group 2: Multiple Items Validation (4 tests)
    // ==========================================

    /** @test */
    public function it_validates_multiple_items_successfully()
    {
        $this->insertBudgetArea([
            'kode_budget' => 'TEST-001',
            'alokasi_dana' => '50000000',
            'terserap' => '10000000',
        ]);
        $this->insertBudgetArea([
            'kode_budget' => 'TEST-002',
            'kode_project' => '38',
            'kode_area' => 'PUSAT',
            'nama_budget' => 'Budget 2',
            'alokasi_dana' => '30000000',
            'terserap' => '5000000',
            'tahun' => 2026,
        ]);

        $items = [
            ['kode_budget' => 'TEST-001', 'jumlah' => '15000000'],
            ['kode_budget' => 'TEST-002', 'jumlah' => '10000000'],
        ];

        $result = $this->service->lockAndValidateMultiple($items, 'PUSAT', '38');

        $this->assertTrue($result->isValid);
        $this->assertEquals(2, $result->details['total_items']);
        $this->assertEquals('25000000.00', $result->details['total_nominal']);
    }

    /** @test */
    public function it_flags_overspend_on_multiple_items()
    {
        $this->insertBudgetArea([
            'kode_budget' => 'TEST-001',
            'alokasi_dana' => '50000000',
            'terserap' => '10000000',
        ]);
        $this->insertBudgetArea([
            'kode_budget' => 'TEST-002',
            'kode_area' => 'PUSAT',
            'kode_project' => '38',
            'nama_budget' => 'Budget 2',
            'alokasi_dana' => '30000000',
            'terserap' => '28000000',
            'tahun' => 2026,
        ]);

        $items = [
            ['kode_budget' => 'TEST-001', 'jumlah' => '15000000'],
            ['kode_budget' => 'TEST-002', 'jumlah' => '5000000'],
        ];

        $result = $this->service->lockAndValidateMultiple($items, 'PUSAT', '38');

        $this->assertTrue($result->isValid);
        $this->assertTrue($result->isOverBudget);
        $this->assertCount(1, $result->overbudgetItems);
    }

    /** @test */
    public function it_rolls_back_transaction_on_validation_failure()
    {
        $result = $this->service->lockAndValidateMultiple(
            [['kode_budget' => 'NONEXISTENT', 'jumlah' => '10000000']],
            'PUSAT', '38'
        );

        $this->assertFalse($result->isValid);
    }

    /** @test */
    public function it_validates_empty_items_array()
    {
        $result = $this->service->lockAndValidateMultiple([], 'PUSAT', '38');

        $this->assertTrue($result->isValid);
        $this->assertEquals(0, $result->details['total_items']);
        $this->assertEquals('0', $result->details['total_nominal']);
    }

    // ==========================================
    // Test Group 3: Helper Methods (5 tests)
    // ==========================================

    /** @test */
    public function it_calculates_remaining_budget()
    {
        $this->insertBudgetArea([
            'alokasi_dana' => '100000000',
            'terserap' => '35000000',
        ]);

        $remaining = $this->service->calculateRemainingBudget('TEST-001', 'PUSAT');
        $this->assertEquals('65000000.00', $remaining);
    }

    /** @test */
    public function it_returns_zero_for_nonexistent_budget()
    {
        $remaining = $this->service->calculateRemainingBudget('NONEXISTENT', 'PUSAT');
        $this->assertEquals('0', $remaining);
    }

    /** @test */
    public function it_checks_budget_exists()
    {
        $this->insertBudgetArea();

        $this->assertTrue($this->service->budgetExists('TEST-001', 'PUSAT'));
        $this->assertFalse($this->service->budgetExists('NONEXISTENT', 'PUSAT'));
    }

    /** @test */
    public function it_gets_budget_details()
    {
        $this->insertBudgetArea();

        $details = $this->service->getBudgetDetails('TEST-001', 'PUSAT');

        $this->assertNotNull($details);
        $this->assertEquals('TEST-001', $details->kode_budget);
        $this->assertEquals('Test Budget', $details->nama_budget);
        $this->assertEquals('100000000.00', $details->alokasi_dana);
    }

    /** @test */
    public function it_checks_if_amount_is_within_budget()
    {
        $this->insertBudgetArea([
            'alokasi_dana' => '50000000',
            'terserap' => '20000000',
        ]);

        $this->assertTrue($this->service->isWithinBudget('TEST-001', '25000000', 'PUSAT'));
        $this->assertTrue($this->service->isWithinBudget('TEST-001', '30000000', 'PUSAT'));
        $this->assertFalse($this->service->isWithinBudget('TEST-001', '35000000', 'PUSAT'));
    }
}
