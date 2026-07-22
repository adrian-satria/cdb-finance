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

    // ==========================================
    // Test Group 1: Single Item Validation (6 tests)
    // ==========================================

    /** @test */
    public function it_validates_budget_within_ceiling()
    {
        // Arrange
        DB::table('master_budget')->insert([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '100000000',
            'terserap' => '20000000',
            'tahun' => 2026,
        ]);

        // Act
        $result = $this->service->validateBudgetCeiling('TEST-001', '30000000');

        // Assert
        $this->assertTrue($result->isValid);
        $this->assertNull($result->errorMessage);
        $this->assertEquals('30000000', $result->details['diminta']);
    }

    /** @test */
    public function it_fails_when_exceeding_budget_ceiling()
    {
        // Arrange
        DB::table('master_budget')->insert([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '100000000',
            'terserap' => '90000000',
            'tahun' => 2026,
        ]);

        // Act
        $result = $this->service->validateBudgetCeiling('TEST-001', '20000000');

        // Assert
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('tidak mencukupi', $result->errorMessage);
        $this->assertEquals('10000000.00', $result->details['sisa_saldo']);
    }

    /** @test */
    public function it_fails_when_budget_not_found()
    {
        // Act
        $result = $this->service->validateBudgetCeiling('NONEXISTENT', '10000000');

        // Assert
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('tidak ditemukan', $result->errorMessage);
    }

    /** @test */
    public function it_validates_exact_remaining_amount()
    {
        // Arrange
        DB::table('master_budget')->insert([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '50000000',
            'terserap' => '20000000',
            'tahun' => 2026,
        ]);

        // Act - Request exactly what's remaining (30M)
        $result = $this->service->validateBudgetCeiling('TEST-001', '30000000');

        // Assert
        $this->assertTrue($result->isValid);
    }

    /** @test */
    public function it_handles_zero_terserap_budget()
    {
        // Arrange
        DB::table('master_budget')->insert([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'nama_budget' => 'Fresh Budget',
            'alokasi_dana' => '75000000',
            'terserap' => '0',
            'tahun' => 2026,
        ]);

        // Act
        $result = $this->service->validateBudgetCeiling('TEST-001', '50000000');

        // Assert
        $this->assertTrue($result->isValid);
        $this->assertEquals('75000000.00', $result->details['sisa_saldo']);
    }

    /** @test */
    public function it_handles_fully_utilized_budget()
    {
        // Arrange
        DB::table('master_budget')->insert([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'nama_budget' => 'Full Budget',
            'alokasi_dana' => '50000000',
            'terserap' => '50000000',
            'tahun' => 2026,
        ]);

        // Act
        $result = $this->service->validateBudgetCeiling('TEST-001', '1000');

        // Assert
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('tidak mencukupi', $result->errorMessage);
    }

    // ==========================================
    // Test Group 2: Multiple Items Validation (4 tests)
    // ==========================================

    /** @test */
    public function it_validates_multiple_items_successfully()
    {
        // Arrange
        DB::table('master_budget')->insert([
            [
                'kode_budget' => 'TEST-001',
                'kode_project' => '38',
                'nama_budget' => 'Budget 1',
                'alokasi_dana' => '50000000',
                'terserap' => '10000000',
                'tahun' => 2026,
            ],
            [
                'kode_budget' => 'TEST-002',
                'kode_project' => '38',
                'nama_budget' => 'Budget 2',
                'alokasi_dana' => '30000000',
                'terserap' => '5000000',
                'tahun' => 2026,
            ],
        ]);

        $items = [
            ['kode_budget' => 'TEST-001', 'jumlah' => '15000000'],
            ['kode_budget' => 'TEST-002', 'jumlah' => '10000000'],
        ];

        // Act
        $result = $this->service->lockAndValidateMultiple($items);

        // Assert
        $this->assertTrue($result->isValid);
        $this->assertEquals(2, $result->details['total_items']);
        $this->assertEquals('25000000.00', $result->details['total_nominal']);
    }

    /** @test */
    public function it_fails_multiple_items_when_one_exceeds_budget()
    {
        // Arrange
        DB::table('master_budget')->insert([
            [
                'kode_budget' => 'TEST-001',
                'kode_project' => '38',
                'nama_budget' => 'Budget 1',
                'alokasi_dana' => '50000000',
                'terserap' => '10000000',
                'tahun' => 2026,
            ],
            [
                'kode_budget' => 'TEST-002',
                'kode_project' => '38',
                'nama_budget' => 'Budget 2',
                'alokasi_dana' => '30000000',
                'terserap' => '28000000', // Only 2M left
                'tahun' => 2026,
            ],
        ]);

        $items = [
            ['kode_budget' => 'TEST-001', 'jumlah' => '15000000'], // Valid
            ['kode_budget' => 'TEST-002', 'jumlah' => '5000000'],  // Exceeds (only 2M left)
        ];

        // Act
        $result = $this->service->lockAndValidateMultiple($items);

        // Assert
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('Budget 2', $result->errorMessage);
    }

    /** @test */
    public function it_rolls_back_transaction_on_validation_failure()
    {
        // Arrange
        DB::table('master_budget')->insert([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '50000000',
            'terserap' => '45000000',
            'tahun' => 2026,
        ]);

        $items = [
            ['kode_budget' => 'TEST-001', 'jumlah' => '10000000'], // Exceeds
        ];

        // Act
        $result = $this->service->lockAndValidateMultiple($items);

        // Assert
        $this->assertFalse($result->isValid);

        // Verify inner transaction was rolled back (outer is handled by DatabaseTransactions)
        $this->assertEquals(1, DB::transactionLevel());
    }

    /** @test */
    public function it_validates_empty_items_array()
    {
        // Act
        $result = $this->service->lockAndValidateMultiple([]);

        // Assert
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
        // Arrange
        DB::table('master_budget')->insert([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '100000000',
            'terserap' => '35000000',
            'tahun' => 2026,
        ]);

        // Act
        $remaining = $this->service->calculateRemainingBudget('TEST-001');

        // Assert
        $this->assertEquals('65000000.00', $remaining);
    }

    /** @test */
    public function it_returns_zero_for_nonexistent_budget()
    {
        // Act
        $remaining = $this->service->calculateRemainingBudget('NONEXISTENT');

        // Assert
        $this->assertEquals('0', $remaining);
    }

    /** @test */
    public function it_checks_budget_exists()
    {
        // Arrange
        DB::table('master_budget')->insert([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '50000000',
            'terserap' => '0',
            'tahun' => 2026,
        ]);

        // Act & Assert
        $this->assertTrue($this->service->budgetExists('TEST-001'));
        $this->assertFalse($this->service->budgetExists('NONEXISTENT'));
    }

    /** @test */
    public function it_gets_budget_details()
    {
        // Arrange
        DB::table('master_budget')->insert([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '50000000',
            'terserap' => '10000000',
            'tahun' => 2026,
        ]);

        // Act
        $details = $this->service->getBudgetDetails('TEST-001');

        // Assert
        $this->assertNotNull($details);
        $this->assertEquals('TEST-001', $details->kode_budget);
        $this->assertEquals('Test Budget', $details->nama_budget);
        $this->assertEquals('50000000.00', $details->alokasi_dana);
    }

    /** @test */
    public function it_checks_if_amount_is_within_budget()
    {
        // Arrange
        DB::table('master_budget')->insert([
            'kode_budget' => 'TEST-001',
            'kode_project' => '38',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '50000000',
            'terserap' => '20000000',
            'tahun' => 2026,
        ]);

        // Act & Assert
        $this->assertTrue($this->service->isWithinBudget('TEST-001', '25000000'));  // Within
        $this->assertTrue($this->service->isWithinBudget('TEST-001', '30000000'));  // Exact
        $this->assertFalse($this->service->isWithinBudget('TEST-001', '35000000')); // Exceeds
    }
}
