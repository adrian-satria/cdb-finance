<?php

namespace Tests\Unit\Observers;

use App\Models\SuratPermintaan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SppObserverTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $userId = DB::table('users')->insertGetId([
            'name' => 'Observer Test',
            'username' => 'observer_test',
            'email' => 'observer@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        session(['role' => 'TESTER']);
    }

    /** @test */
    public function it_logs_audit_on_spp_creation()
    {
        $spp = SuratPermintaan::create([
            'no_surat' => 'OBS-TEST-001',
            'tanggal' => now(),
            'jenis_permintaan' => 'PROJECT',
            'kode_project' => '38',
            'status_surat' => 'Pending',
            'posisi_saat_ini' => 'MANAGER_KEUANGAN',
        ]);

        $log = DB::table('audit_trails')->where('aksi', 'INSERT_SPP')->where('deskripsi', 'like', '%OBS-TEST-001%')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('OBS-TEST-001', $log->deskripsi);
    }

    /** @test */
    public function it_logs_audit_on_spp_update()
    {
        SuratPermintaan::create([
            'no_surat' => 'OBS-TEST-002',
            'tanggal' => now(),
            'jenis_permintaan' => 'PROJECT',
            'kode_project' => '38',
            'status_surat' => 'Pending',
            'posisi_saat_ini' => 'MANAGER_KEUANGAN',
        ]);

        $spp = SuratPermintaan::find('OBS-TEST-002');
        $spp->update(['status_surat' => 'Approved', 'posisi_saat_ini' => 'KASIR_PUSAT']);

        $updateLog = DB::table('audit_trails')->where('aksi', 'UPDATE_SPP')->first();
        $this->assertNotNull($updateLog);

        $historyLog = DB::table('audit_trails')->where('aksi', 'WORKFLOW_TRANSITION')->first();
        $this->assertNotNull($historyLog);
    }

    /** @test */
    public function it_logs_spp_history_on_status_change()
    {
        SuratPermintaan::create([
            'no_surat' => 'OBS-TEST-003',
            'tanggal' => now(),
            'jenis_permintaan' => 'PROJECT',
            'kode_project' => '38',
            'status_surat' => 'Pending',
            'posisi_saat_ini' => 'MANAGER_KEUANGAN',
        ]);

        $spp = SuratPermintaan::find('OBS-TEST-003');
        $spp->update(['status_surat' => 'Approved']);

        $history = DB::table('spp_history')
            ->where('no_surat', 'OBS-TEST-003')
            ->first();
        $this->assertNotNull($history);
        $this->assertEquals('Pending', $history->status_dari);
        $this->assertEquals('Approved', $history->status_ke);
    }

    /** @test */
    public function it_logs_audit_on_spp_deletion()
    {
        SuratPermintaan::create([
            'no_surat' => 'OBS-TEST-004',
            'tanggal' => now(),
            'jenis_permintaan' => 'PROJECT',
            'kode_project' => '38',
            'status_surat' => 'Rejected',
            'posisi_saat_ini' => 'REJECTED',
        ]);

        $spp = SuratPermintaan::find('OBS-TEST-004');
        $spp->delete();

        $deleteLog = DB::table('audit_trails')->where('aksi', 'DELETE_SPP')->first();
        $this->assertNotNull($deleteLog);
        $this->assertStringContainsString('OBS-TEST-004', $deleteLog->deskripsi);
    }

    /** @test */
    public function it_captures_position_changes_in_audit_trail()
    {
        SuratPermintaan::create([
            'no_surat' => 'OBS-TEST-005',
            'tanggal' => now(),
            'jenis_permintaan' => 'PROJECT',
            'kode_project' => '38',
            'status_surat' => 'Pending',
            'posisi_saat_ini' => 'AREA_MANAGER',
        ]);

        $spp = SuratPermintaan::find('OBS-TEST-005');
        $spp->update(['posisi_saat_ini' => 'FINANCE_PROJECT']);

        $transitionLog = DB::table('audit_trails')
            ->where('aksi', 'WORKFLOW_TRANSITION')
            ->where('deskripsi', 'like', '%OBS-TEST-005%')
            ->first();
        $this->assertNotNull($transitionLog);
        $this->assertStringContainsString('AREA_MANAGER', $transitionLog->deskripsi);
        $this->assertStringContainsString('FINANCE_PROJECT', $transitionLog->deskripsi);
    }
}
