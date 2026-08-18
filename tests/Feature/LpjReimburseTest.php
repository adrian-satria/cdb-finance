<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LpjReimburseTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('project')->upsert(['kode_project' => '01', 'nama_project' => 'Project 01'], 'kode_project');
        DB::table('project')->upsert(['kode_project' => '40', 'nama_project' => 'Project 40'], 'kode_project');

        $this->seedUser('lpj.maker', 'MAKER', '01', 'belu');
        $this->seedUser('lpj.koord', 'KOORDINATOR_KEUANGAN', '01', 'belu');
        $this->seedUser('lpj.mk', 'MANAGER_KEUANGAN', '01', 'belu');
        $this->seedUser('lpj.kasir', 'KASIR_PUSAT', 'all', 'all');
    }

    private function seedUser(string $username, string $role, string $project, string $area): void
    {
        $id = DB::table('users')->insertGetId([
            'name' => $username, 'username' => $username, 'password' => bcrypt('password'),
            'email' => $username.'@test.com', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $id)->update(['id_user' => $id]);
        DB::table('user_access')->insert([
            'id_user' => $id, 'role' => $role, 'jabatan' => $role, 'kode_area' => $area, 'kode_project' => $project,
        ]);
    }

    private function actingAsUser(string $username, string $role, string $project, string $area): static
    {
        $user = DB::table('users')->where('username', $username)->first();

        return $this->actingAs(\App\Models\User::find($user->id))->withSession([
            'role' => $role, 'kode_area' => $area, 'kode_project' => $project,
        ]);
    }

    private function cairUm(string $total): string
    {
        $this->actingAsUser('lpj.maker', 'MAKER', '01', 'belu')->post('/uang-muka/simpan', [
            'tanggal' => '2026-08-18', 'kode_project' => '01', 'kode_area' => 'belu',
            'items' => [['kode_budget' => 'B-001', 'jumlah' => $total, 'keterangan' => 'x']],
        ]);
        $noAju = DB::table('pengajuan_uang_muka')->orderByDesc('created_at')->value('no_aju');
        $this->actingAsUser('lpj.koord', 'KOORDINATOR_KEUANGAN', '01', 'belu')->post('/uang-muka/validasi', ['no_aju' => $noAju, 'aksi' => 'approve']);
        $this->actingAsUser('lpj.mk', 'MANAGER_KEUANGAN', '01', 'belu')->post('/uang-muka/validasi', ['no_aju' => $noAju, 'aksi' => 'approve']);
        $this->actingAsUser('lpj.kasir', 'KASIR_PUSAT', 'all', 'all')->post('/uang-muka/cairkan', ['no_aju' => $noAju]);

        return $noAju;
    }

    /** @test */
    public function lpj_under_realization_sets_refund_and_reimburse_when_over(): void
    {
        // Realisasi < UM -> refund (sisa>0). 
        $noAju = $this->cairUm('1000000');

        $this->actingAsUser('lpj.maker', 'MAKER', '01', 'belu')->post('/uang-muka/lpj/simpan', [
            'no_aju' => $noAju, 'tanggal' => '2026-08-19',
            'items' => [['kode_budget' => 'B-001', 'jumlah' => 600000, 'keterangan' => 'real']],
        ]);
        $noLpj = DB::table('lpj_uang_muka')->orderByDesc('created_at')->value('no_lpj');

        $lpj = DB::table('lpj_uang_muka')->where('no_lpj', $noLpj)->first();
        $this->assertEquals('400000.00', $lpj->selisih); // refund

        $this->actingAsUser('lpj.maker', 'MAKER', '01', 'belu')->post('/uang-muka/lpj/validasi', ['no_lpj' => $noLpj, 'aksi' => 'approve']);
        $this->actingAsUser('lpj.mk', 'MANAGER_KEUANGAN', '01', 'belu')->post('/uang-muka/lpj/validasi', ['no_lpj' => $noLpj, 'aksi' => 'approve']);
        $this->actingAsUser('lpj.kasir', 'KASIR_PUSAT', 'all', 'all')->post('/uang-muka/lpj/validasi', ['no_lpj' => $noLpj, 'aksi' => 'approve']);

        $lpj = DB::table('lpj_uang_muka')->where('no_lpj', $noLpj)->first();
        $this->assertEquals('Approved', $lpj->status_lpj);
        // UM sisa = selisih (refund) karena realisasi < UM
        $this->assertEquals('400000.00', DB::table('pengajuan_uang_muka')->where('no_aju', $noAju)->value('sisa_lpj'));
        // Tidak ada reimburse otomatis
        $this->assertEquals(0, DB::table('reimburse_lpj')->where('no_lpj', $noLpj)->count());
    }

    /** @test */
    public function lpj_over_realization_auto_creates_reimburse(): void
    {
        $noAju = $this->cairUm('1000000');

        $this->actingAsUser('lpj.maker', 'MAKER', '01', 'belu')->post('/uang-muka/lpj/simpan', [
            'no_aju' => $noAju, 'tanggal' => '2026-08-19',
            'items' => [['kode_budget' => 'B-001', 'jumlah' => 1500000, 'keterangan' => 'real']],
        ]);
        $noLpj = DB::table('lpj_uang_muka')->orderByDesc('created_at')->value('no_lpj');

        $lpj = DB::table('lpj_uang_muka')->where('no_lpj', $noLpj)->first();
        $this->assertEquals('-500000.00', $lpj->selisih); // reimburse 500rb

        $this->actingAsUser('lpj.maker', 'MAKER', '01', 'belu')->post('/uang-muka/lpj/validasi', ['no_lpj' => $noLpj, 'aksi' => 'approve']);
        $this->actingAsUser('lpj.mk', 'MANAGER_KEUANGAN', '01', 'belu')->post('/uang-muka/lpj/validasi', ['no_lpj' => $noLpj, 'aksi' => 'approve']);
        $this->actingAsUser('lpj.kasir', 'KASIR_PUSAT', 'all', 'all')->post('/uang-muka/lpj/validasi', ['no_lpj' => $noLpj, 'aksi' => 'approve']);

        $reimburse = DB::table('reimburse_lpj')->where('no_lpj', $noLpj)->first();
        $this->assertNotNull($reimburse);
        $this->assertEquals('500000.00', $reimburse->total_nominal);
        $this->assertEquals('Pending', $reimburse->status_reimburse);
        $this->assertEquals('MANAGER_KEUANGAN', $reimburse->posisi_saat_ini);

        // Approve reimburse
        $this->actingAsUser('lpj.mk', 'MANAGER_KEUANGAN', '01', 'belu')->post('/uang-muka/reimburse/validasi', ['no_reimburse' => $reimburse->no_reimburse, 'aksi' => 'approve']);
        $this->actingAsUser('lpj.kasir', 'KASIR_PUSAT', 'all', 'all')->post('/uang-muka/reimburse/validasi', ['no_reimburse' => $reimburse->no_reimburse, 'aksi' => 'approve']);

        $reimburse = DB::table('reimburse_lpj')->where('no_reimburse', $reimburse->no_reimburse)->first();
        $this->assertEquals('Approved', $reimburse->status_reimburse);
        // UM lunas (sisa 0)
        $this->assertEquals('0.00', DB::table('pengajuan_uang_muka')->where('no_aju', $noAju)->value('sisa_lpj'));
    }
}
