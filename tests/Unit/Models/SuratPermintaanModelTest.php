<?php

namespace Tests\Unit\Models;

use App\Models\SuratPermintaan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SuratPermintaanModelTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('project')->upsert(
            ['kode_project' => 'MOD01', 'nama_project' => 'Model Test'],
            'kode_project'
        );
    }

    private function createSpp(array $overrides = []): SuratPermintaan
    {
        return SuratPermintaan::create(array_merge([
            'no_surat' => 'MOD-TEST-'.uniqid(),
            'tanggal' => now(),
            'kode_project' => 'MOD01',
            'status_surat' => 'Pending',
            'posisi_saat_ini' => 'MANAGER_KEUANGAN',
            'total_nominal' => '5000000',
        ], $overrides));
    }

    /** @test */
    public function scope_pending_returns_only_pending_spp()
    {
        $this->createSpp(['no_surat' => 'SCP-001', 'status_surat' => 'Pending']);
        $this->createSpp(['no_surat' => 'SCP-002', 'status_surat' => 'Approved']);

        $result = SuratPermintaan::pending()->get();
        $this->assertCount(1, $result);
        $this->assertEquals('SCP-001', $result->first()->no_surat);
    }

    /** @test */
    public function scope_approved_returns_only_approved_spp()
    {
        $this->createSpp(['no_surat' => 'SCA-001', 'status_surat' => 'Approved']);
        $result = SuratPermintaan::approved()->get();
        $this->assertCount(1, $result);
    }

    /** @test */
    public function scope_disbursed_returns_only_disbursed_spp()
    {
        $spp = $this->createSpp(['no_surat' => 'SCD-001', 'status_surat' => 'Disbursed']);
        $result = SuratPermintaan::disbursed()->where('no_surat', 'SCD-001')->get();
        $this->assertCount(1, $result);
        $this->assertEquals('SCD-001', $result->first()->no_surat);
    }

    /** @test */
    public function scope_awaiting_my_approval_filters_by_role()
    {
        $this->createSpp(['no_surat' => 'SMA-001', 'posisi_saat_ini' => 'MANAGER_KEUANGAN', 'status_surat' => 'Pending']);
        $result = SuratPermintaan::awaitingMyApproval('MANAGER_KEUANGAN')->get();
        $this->assertCount(1, $result);
    }

    /** @test */
    public function total_nominal_formatted_includes_rp()
    {
        $spp = $this->createSpp(['total_nominal' => '15000000']);
        $this->assertStringContainsString('Rp', $spp->total_nominal_formatted);
    }

    /** @test */
    public function can_be_approved_by_checks_position()
    {
        $spp = $this->createSpp(['posisi_saat_ini' => 'MANAGER_KEUANGAN']);
        $this->assertTrue($spp->canBeApprovedBy('MANAGER_KEUANGAN'));
        $this->assertFalse($spp->canBeApprovedBy('DIREKTUR'));
    }

    /** @test */
    public function admin_can_approve_any_workflow()
    {
        $spp = $this->createSpp(['posisi_saat_ini' => 'MANAGER_KEUANGAN']);
        $this->assertTrue($spp->canBeApprovedBy('ADMIN'));
    }

    /** @test */
    public function route_to_updates_position()
    {
        $spp = $this->createSpp();
        $spp->routeTo('KASIR_PUSAT');
        $this->assertEquals('KASIR_PUSAT', $spp->fresh()->posisi_saat_ini);
    }

    /** @test */
    public function mark_as_disbursed_updates_status()
    {
        $spp = $this->createSpp(['status_surat' => 'Approved', 'posisi_saat_ini' => 'KASIR_PUSAT']);
        $spp->markAsDisbursed();
        $this->assertEquals('Disbursed', $spp->fresh()->status_surat);
    }

    /** @test */
    public function has_maker_checks_user_id()
    {
        $spp = $this->createSpp(['id_maker' => 1]);
        $this->assertTrue($spp->hasMaker(1));
        $this->assertFalse($spp->hasMaker(99));
    }

    /** @test */
    public function update_workflow_sets_new_status_and_position()
    {
        $spp = $this->createSpp();
        $spp->updateWorkflow('Approved', 'KASIR_PUSAT', 'OK');
        $fresh = $spp->fresh();
        $this->assertEquals('Approved', $fresh->status_surat);
        $this->assertEquals('KASIR_PUSAT', $fresh->posisi_saat_ini);
        $this->assertEquals('OK', $fresh->keterangan_checker);
    }
}
