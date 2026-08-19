<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdvanceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('project')->upsert(['kode_project' => '01', 'nama_project' => 'Project 01'], 'kode_project');
        DB::table('project')->upsert(['kode_project' => '40', 'nama_project' => 'Project 40'], 'kode_project');

        $this->seedUser('um.maker', 'MAKER', '01', 'belu');
        $this->seedUser('um.koord', 'KOORDINATOR_KEUANGAN', '01', 'belu');
        $this->seedUser('um.mk', 'MANAGER_KEUANGAN', '01', 'belu');
        $this->seedUser('um.kasir', 'KASIR_PUSAT', 'all', 'all');
        $this->seedUser('lpj.mk', 'MANAGER_KEUANGAN', '01', 'belu');
        $this->seedUser('lpj.koord', 'KOORDINATOR_KEUANGAN', '01', 'belu');
    }

    private function seedUser(string $username, string $role, string $project, string $area): int
    {
        $id = DB::table('users')->insertGetId([
            'name' => $username, 'username' => $username, 'password' => bcrypt('password'),
            'email' => $username.'@test.com', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $id)->update(['id_user' => $id]);
        DB::table('user_access')->insert([
            'id_user' => $id, 'role' => $role, 'jabatan' => $role,
            'kode_area' => $area, 'kode_project' => $project,
        ]);

        return $id;
    }

    private function actingAsUser(string $username, string $role, string $project, string $area): static
    {
        $user = DB::table('users')->where('username', $username)->first();

        return $this->actingAs(\App\Models\User::find($user->id))->withSession([
            'role' => $role, 'kode_area' => $area, 'kode_project' => $project,
        ]);
    }

    private function storeUm(string $maker): string
    {
        $resp = $this->actingAsUser($maker, 'MAKER', '01', 'belu')->post('/uang-muka/simpan', [
            'tanggal' => '2026-08-18',
            'kode_project' => '01',
            'kode_area' => 'belu',
            'keterangan' => 'UM test',
            'items' => [['kode_budget' => 'B-001', 'jumlah' => 1000000, 'keterangan' => 'keperluan']],
        ]);

        $um = DB::table('pengajuan_uang_muka')->orderByDesc('created_at')->first();

        return $um->no_aju;
    }

    private function cairUm(string $total): string
    {
        $noAju = $this->storeUm('um.maker');
        $this->actingAsUser('um.koord', 'KOORDINATOR_KEUANGAN', '01', 'belu')->post('/uang-muka/validasi', ['no_aju' => $noAju, 'aksi' => 'approve']);
        $this->actingAsUser('um.mk', 'MANAGER_KEUANGAN', '01', 'belu')->post('/uang-muka/validasi', ['no_aju' => $noAju, 'aksi' => 'approve']);
        $this->actingAsUser('um.kasir', 'KASIR_PUSAT', 'all', 'all')->post('/uang-muka/cairkan', ['no_aju' => $noAju]);

        return $noAju;
    }

    /** @test */
    public function maker_can_submit_um_and_flow_to_disburse(): void
    {
        $noAju = $this->storeUm('um.maker');

        $um = DB::table('pengajuan_uang_muka')->where('no_aju', $noAju)->first();
        $this->assertEquals('Pending', $um->status_um);
        $this->assertEquals('KOORDINATOR_KEUANGAN', $um->posisi_saat_ini);

        // KOORDINATOR_KEUANGAN approve
        $this->actingAsUser('um.koord', 'KOORDINATOR_KEUANGAN', '01', 'belu')
            ->post('/uang-muka/validasi', ['no_aju' => $noAju, 'aksi' => 'approve']);
        $this->assertEquals('MANAGER_KEUANGAN', DB::table('pengajuan_uang_muka')->where('no_aju', $noAju)->value('posisi_saat_ini'));

        // MANAGER_KEUANGAN approve (under threshold -> KASIR_PUSAT, Approved)
        $this->actingAsUser('um.mk', 'MANAGER_KEUANGAN', '01', 'belu')
            ->post('/uang-muka/validasi', ['no_aju' => $noAju, 'aksi' => 'approve']);
        $um = DB::table('pengajuan_uang_muka')->where('no_aju', $noAju)->first();
        $this->assertEquals('Approved', $um->status_um);
        $this->assertEquals('KASIR_PUSAT', $um->posisi_saat_ini);

        // KASIR_PUSAT cairkan
        $this->actingAsUser('um.kasir', 'KASIR_PUSAT', 'all', 'all')
            ->post('/uang-muka/cairkan', ['no_aju' => $noAju]);

        $um = DB::table('pengajuan_uang_muka')->where('no_aju', $noAju)->first();
        $this->assertEquals('Cair', $um->status_um);
        $this->assertEquals('1000000.00', $um->sisa_lpj);
        $this->assertNotNull($um->tanggal_jatuh_tempo);
        $this->assertNotNull($um->tanggal_cair);
    }

    /** @test */
    public function out_of_scope_user_cannot_validate_um(): void
    {
        $noAju = $this->storeUm('um.maker');

        // KOORDINATOR of project 40 (different project) must be rejected
        $this->seedUser('um.koord40', 'KOORDINATOR_KEUANGAN', '40', 'belu');
        $resp = $this->actingAsUser('um.koord40', 'KOORDINATOR_KEUANGAN', '40', 'belu')
            ->post('/uang-muka/validasi', ['no_aju' => $noAju, 'aksi' => 'approve']);

        $resp->assertSessionHas('error');
        $this->assertEquals('KOORDINATOR_KEUANGAN', DB::table('pengajuan_uang_muka')->where('no_aju', $noAju)->value('posisi_saat_ini'));
    }

    /** @test */
    public function finance_can_record_refund_and_clears_piutang(): void
    {
        $noAju = $this->cairUm('1000000');
        $file = \Illuminate\Http\UploadedFile::fake()->image('bukti.png');

        $this->actingAsUser('lpj.mk', 'MANAGER_KEUANGAN', '01', 'belu')
            ->post('/uang-muka/refund', [
                'no_aju' => $noAju,
                'nominal' => 1000000,
                'refund_tanggal' => '2026-08-20',
                'file_lampiran' => [$file],
            ]);

        $um = DB::table('pengajuan_uang_muka')->where('no_aju', $noAju)->first();
        $this->assertEquals('0.00', $um->sisa_lpj);
        $this->assertEquals('1000000.00', $um->refund_jumlah);
        $this->assertNotNull($um->refund_bukti);
        $this->assertEquals(1, DB::table('pengajuan_uang_muka_files')->where('no_aju', $noAju)->where('kategori', 'REFUND')->count());
    }

    /** @test */
    public function non_finance_cannot_record_refund(): void
    {
        $noAju = $this->cairUm('1000000');

        $resp = $this->actingAsUser('lpj.koord', 'KOORDINATOR_KEUANGAN', '01', 'belu')
            ->post('/uang-muka/refund', ['no_aju' => $noAju, 'nominal' => 1000000, 'refund_tanggal' => '2026-08-20']);

        $resp->assertStatus(403);
        $um = DB::table('pengajuan_uang_muka')->where('no_aju', $noAju)->first();
        $this->assertEquals('1000000.00', $um->sisa_lpj);
    }
}
