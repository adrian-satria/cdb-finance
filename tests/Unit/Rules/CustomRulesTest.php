<?php

namespace Tests\Unit\Rules;

use App\Rules\BudgetExists;
use App\Rules\ProjectExists;
use App\Rules\ValidRoleForUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomRulesTest extends TestCase
{
    use DatabaseTransactions;

    // ==========================================
    // BudgetExists (4 tests)
    // ==========================================

    /** @test */
    public function budget_exists_passes_when_budget_found()
    {
        DB::table('master_budget')->insert([
            'kode_budget' => 'B001',
            'kode_project' => '38',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '100000',
            'terserap' => '0',
        ]);

        $rule = new BudgetExists;
        $this->assertTrue($rule->passes('kode_budget', 'B001'));
    }

    /** @test */
    public function budget_exists_fails_when_budget_not_found()
    {
        $rule = new BudgetExists;
        $this->assertFalse($rule->passes('kode_budget', 'NONEXISTENT'));
    }

    /** @test */
    public function budget_exists_returns_correct_message()
    {
        $rule = new BudgetExists;
        $message = $rule->message();
        $this->assertStringContainsString('tidak ditemukan', $message);
    }

    /** @test */
    public function budget_exists_handles_null_value()
    {
        $rule = new BudgetExists;
        $this->assertFalse($rule->passes('kode_budget', null));
    }

    // ==========================================
    // ProjectExists (4 tests)
    // ==========================================

    /** @test */
    public function project_exists_passes_when_project_found()
    {
        $rule = new ProjectExists;
        $this->assertTrue($rule->passes('kode_project', '38'));
    }

    /** @test */
    public function project_exists_fails_when_project_not_found()
    {
        $rule = new ProjectExists;
        $this->assertFalse($rule->passes('kode_project', '99'));
    }

    /** @test */
    public function project_exists_returns_correct_message()
    {
        $rule = new ProjectExists;
        $this->assertStringContainsString('tidak ditemukan', $rule->message());
    }

    /** @test */
    public function project_exists_fails_for_null_value()
    {
        $rule = new ProjectExists;
        $this->assertFalse($rule->passes('kode_project', null));
    }

    // ==========================================
    // ValidRoleForUser (6 tests)
    // ==========================================

    /** @test */
    public function valid_role_passes_for_admin()
    {
        $rule = new ValidRoleForUser;
        $this->assertTrue($rule->passes('role', 'ADMIN'));
    }

    /** @test */
    public function valid_role_passes_for_maker()
    {
        $rule = new ValidRoleForUser;
        $this->assertTrue($rule->passes('role', 'MAKER'));
    }

    /** @test */
    public function valid_role_passes_for_all_valid_roles()
    {
        $validRoles = [
            'ADMIN', 'MAKER', 'CHECKER', 'KASIR_PUSAT', 'DIREKTUR',
            'MANAGER_KEUANGAN', 'AREA_MANAGER', 'FINANCE_PROJECT',
            'PROJECT_MANAGER', 'MANAGER_PKP',
            'KOORDINATOR_KEUANGAN', 'KOORDINATOR_PK', 'KOORDINATOR_TC',
            'KOORDINATOR_DIKLAT', 'KOORDINATOR_KLINIK', 'KOORDINATOR_BATRA',
            'KOORDINATOR_BIDANG',
        ];

        $rule = new ValidRoleForUser;
        foreach ($validRoles as $role) {
            $this->assertTrue($rule->passes('role', $role), "Role {$role} should be valid");
        }
    }

    /** @test */
    public function valid_role_fails_for_invalid_role()
    {
        $rule = new ValidRoleForUser;
        $this->assertFalse($rule->passes('role', 'SUPER_ADMIN'));
    }

    /** @test */
    public function valid_role_fails_for_empty_string()
    {
        $rule = new ValidRoleForUser;
        $this->assertFalse($rule->passes('role', ''));
    }

    /** @test */
    public function valid_role_returns_correct_message()
    {
        $rule = new ValidRoleForUser;
        $this->assertStringContainsString('tidak valid', $rule->message());
    }
}
