<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SppE2eFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected string $noSurat = '';
    protected int $makerId;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('project')->upsert(
            ['kode_project' => '40', 'nama_project' => 'E2E Project'],
            'kode_project'
        );

        DB::table('budget_area')->updateOrInsert(
            ['kode_project' => '40', 'kode_area' => 'belu', 'kode_budget' => '1.1.2', 'tahun' => '2026'],
            [
                'nama_budget' => 'FGD RAD HIV AIDS',
                'alokasi_dana' => '30000000',
                'terserap' => '0',
                'terserap_sementara' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->makerId = $this->seedUser('maker.e2e', 'MAKER');
        $this->seedUser('am.e2e', 'AREA_MANAGER');
        $this->seedUser('fp.e2e', 'FINANCE_PROJECT');
        $this->seedUser('pm.e2e', 'PROJECT_MANAGER');
        $this->seedUser('mk.e2e', 'MANAGER_KEUANGAN');
        $this->seedUser('kp.e2e', 'KASIR_PUSAT');
    }

    private function seedUser(string $username, string $role): int
    {
        $id = DB::table('users')->insertGetId([
            'name' => ucfirst($username),
            'username' => $username,
            'password' => bcrypt('password'),
            'email' => $username.'@test.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $id)->update(['id_user' => $id]);
        DB::table('user_access')->insert([
            'id_user' => $id,
            'role' => $role,
            'jabatan' => $role,
            'kode_area' => $role === 'KASIR_PUSAT' ? null : 'belu',
            'kode_project' => $role === 'KASIR_PUSAT' ? null : '40',
        ]);

        return $id;
    }

    private function actingAsRole(string $username): static
    {
        $user = DB::table('users')->where('username', $username)->first();
        $access = DB::table('user_access')->where('id_user', $user->id)->first();

        return $this->actingAs(\App\Models\User::find($user->id))->withSession([
            'role' => $access->role,
            'kode_area' => $access->kode_area,
            'kode_project' => $access->kode_project,
        ]);
    }

    private function createSpp(int $nominal = 5000000): void
    {
        $response = $this->actingAsRole('maker.e2e')->post('/spp/simpan', [
            'tanggal' => '2026-08-10',
            'kode_project' => '40',
            'kode_area' => 'belu',
            'sumber_dana' => 'BANK-1',
            'bank_tujuan' => 'BCA',
            'no_rekening_tujuan' => '1234567890',
            'nama_rekening_tujuan' => 'Agnes',
            'items' => [
                ['kode_budget' => '1.1.2', 'jumlah' => (string) $nominal, 'keterangan' => 'Kegiatan FGD'],
            ],
        ]);

        $response->assertRedirect('/spp');
        $response->assertSessionHas('success');

        $this->noSurat = DB::table('surat_permintaan')
            ->where('id_maker', $this->makerId)
            ->orderByDesc('created_at')
            ->first()->no_surat;
    }

    private function approveAs(string $username): void
    {
        $this->actingAsRole($username)->post('/spp/validasi', [
            'no_surat' => $this->noSurat,
            'aksi' => 'approve',
            'alasan' => 'Setuju',
        ])->assertRedirect('/spp');
    }

    /** @test */
    public function full_approve_then_disburse_commits_budget_once(): void
    {
        $this->createSpp(5000000);

        $reserved = DB::table('budget_area')
            ->where('kode_budget', '1.1.2')->where('kode_area', 'belu')
            ->value('terserap_sementara');
        $this->assertEquals('5000000.00', (string) $reserved);

        $this->approveAs('am.e2e');
        $this->approveAs('fp.e2e');
        $this->approveAs('pm.e2e');
        $this->approveAs('mk.e2e');

        $surat = DB::table('surat_permintaan')->where('no_surat', $this->noSurat)->first();
        $this->assertEquals('Approved', $surat->status_surat);
        $this->assertEquals('KASIR_PUSAT', $surat->posisi_saat_ini);

        $this->actingAsRole('kp.e2e')->post('/spp/cairkan', [
            'no_surat' => $this->noSurat,
            'biaya_admin' => '0',
        ])->assertRedirect('/spp');

        $budget = DB::table('budget_area')
            ->where('kode_budget', '1.1.2')->where('kode_area', 'belu')
            ->first();

        $this->assertEquals('5000000.00', (string) $budget->terserap);
        $this->assertEquals('0.00', (string) $budget->terserap_sementara);

        $surat = DB::table('surat_permintaan')->where('no_surat', $this->noSurat)->first();
        $this->assertEquals('Disbursed', $surat->status_surat);
    }

    /** @test */
    public function maker_can_revise_edit_and_resubmit_spp(): void
    {
        $this->createSpp(3000000);

        $this->actingAsRole('am.e2e')->post('/spp/validasi', [
            'no_surat' => $this->noSurat,
            'aksi' => 'revise',
            'alasan' => 'Lampiran kurang lengkap',
        ])->assertRedirect('/spp');

        $surat = DB::table('surat_permintaan')->where('no_surat', $this->noSurat)->first();
        $this->assertEquals('Revisi', $surat->status_surat);
        $this->assertEquals('MAKER', $surat->posisi_saat_ini);

        $reserved = DB::table('budget_area')
            ->where('kode_budget', '1.1.2')->where('kode_area', 'belu')
            ->value('terserap_sementara');
        $this->assertEquals('3000000.00', (string) $reserved);

        $response = $this->actingAsRole('maker.e2e')
            ->get('/spp/edit?no_surat='.rawurlencode($this->noSurat));
        $response->assertStatus(200);
        $response->assertSee('Perbaiki SPP Revisi');
        $response->assertSee('Lampiran kurang lengkap');

        $response = $this->actingAsRole('maker.e2e')->post('/spp/update', [
            'no_surat' => $this->noSurat,
            'kode_project' => '40',
            'kode_area' => 'belu',
            'tanggal' => '2026-08-10',
            'bank_tujuan' => 'BCA',
            'no_rekening_tujuan' => '1234567890',
            'nama_rekening_tujuan' => 'Agnes',
            'items' => [
                ['kode_budget' => '1.1.2', 'jumlah' => 4000000, 'keterangan' => 'Kegiatan FGD (revisi)'],
            ],
        ]);

        $response->assertRedirect('/spp');
        $response->assertSessionHas('success');

        $surat = DB::table('surat_permintaan')->where('no_surat', $this->noSurat)->first();
        $this->assertEquals('Pending', $surat->status_surat);
        $this->assertEquals('AREA_MANAGER', $surat->posisi_saat_ini);
        $this->assertEquals('4000000.00', (string) $surat->total_nominal);

        $budget = DB::table('budget_area')
            ->where('kode_budget', '1.1.2')->where('kode_area', 'belu')
            ->first();
        $this->assertEquals('4000000.00', (string) $budget->terserap_sementara);

        $this->approveAs('am.e2e');
        $this->approveAs('fp.e2e');
        $this->approveAs('pm.e2e');
        $this->approveAs('mk.e2e');

        $surat = DB::table('surat_permintaan')->where('no_surat', $this->noSurat)->first();
        $this->assertEquals('Approved', $surat->status_surat);
    }

    /** @test */
    public function rejected_spp_releases_reservation(): void
    {
        $this->createSpp(2000000);

        $this->actingAsRole('am.e2e')->post('/spp/validasi', [
            'no_surat' => $this->noSurat,
            'aksi' => 'reject',
            'alasan' => 'Tidak disetujui',
        ])->assertRedirect('/spp');

        $budget = DB::table('budget_area')
            ->where('kode_budget', '1.1.2')->where('kode_area', 'belu')
            ->first();
        $this->assertEquals('0.00', (string) $budget->terserap_sementara);
    }
}
