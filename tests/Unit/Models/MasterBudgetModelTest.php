<?php

namespace Tests\Unit\Models;

use App\Models\MasterBudget;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterBudgetModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function remaining_budget_calculates_correctly()
    {
        $budget = MasterBudget::create([
            'kode_budget' => 'MTB-001',
            'kode_project' => '38',
            'nama_budget' => 'Test',
            'alokasi_dana' => '100000000',
        ]);
        DB::table('master_budget')->where('kode_budget', 'MTB-001')->update(['terserap' => '30000000']);
        $budget->refresh();

        $this->assertEquals('70000000.00', $budget->remaining_budget);
    }

    /** @test */
    public function utilization_percentage_returns_correct_value()
    {
        $budget = MasterBudget::create([
            'kode_budget' => 'MTB-002',
            'kode_project' => '38',
            'nama_budget' => 'Test',
            'alokasi_dana' => '100000000',
        ]);
        DB::table('master_budget')->where('kode_budget', 'MTB-002')->update(['terserap' => '25000000']);
        $budget->refresh();

        $this->assertEquals(25.0, $budget->utilization_percentage);
    }

    /** @test */
    public function utilization_returns_zero_when_no_alokasi()
    {
        $budget = MasterBudget::create([
            'kode_budget' => 'MTB-003',
            'kode_project' => '38',
            'nama_budget' => 'Test',
            'alokasi_dana' => '0',
        ]);
        $this->assertEquals(0, $budget->utilization_percentage);
    }

    /** @test */
    public function has_available_funds_checks_correctly()
    {
        $budget = MasterBudget::create([
            'kode_budget' => 'MTB-004',
            'kode_project' => '38',
            'nama_budget' => 'Test',
            'alokasi_dana' => '50000000',
        ]);
        DB::table('master_budget')->where('kode_budget', 'MTB-004')->update(['terserap' => '10000000']);
        $budget->refresh();

        $this->assertTrue($budget->hasAvailableFunds('30000000'));
        $this->assertTrue($budget->hasAvailableFunds('40000000'));
        $this->assertFalse($budget->hasAvailableFunds('50000000'));
    }

    /** @test */
    public function absorb_increments_terserap()
    {
        $budget = MasterBudget::create([
            'kode_budget' => 'MTB-005',
            'kode_project' => '38',
            'nama_budget' => 'Test',
            'alokasi_dana' => '100000000',
            'terserap' => '0',
        ]);
        $budget->absorb('25000000');
        $budget->refresh();

        $this->assertEquals('25000000.00', $budget->terserap);
    }

    /** @test */
    public function release_decrements_terserap()
    {
        $budget = MasterBudget::create([
            'kode_budget' => 'MTB-006',
            'kode_project' => '38',
            'nama_budget' => 'Test',
            'alokasi_dana' => '100000000',
            'terserap' => '50000000',
        ]);
        $budget->release('20000000');
        $budget->refresh();

        $this->assertEquals('30000000.00', $budget->terserap);
    }
}
