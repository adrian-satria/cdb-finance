<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SppScopingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['38', '40'] as $code) {
            DB::table('project')->upsert(
                ['kode_project' => $code, 'nama_project' => 'Project '.$code],
                'kode_project'
            );
        }

        $this->seedUser('coord.x', 'KOORDINATOR_KEUANGAN', '38');
        $this->seedUser('fp.y', 'FINANCE_PROJECT', '40');
    }

    private function seedUser(string $username, string $role, string $project): int
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
            'kode_area' => 'belu',
            'kode_project' => $project,
        ]);

        return $id;
    }

    private function actingAsUser(string $username, string $role, string $project): static
    {
        $user = DB::table('users')->where('username', $username)->first();

        return $this->actingAs(\App\Models\User::find($user->id))->withSession([
            'role' => $role,
            'kode_area' => 'belu',
            'kode_project' => $project,
        ]);
    }

    private function createSpp(string $noSurat, string $project): void
    {
        DB::table('surat_permintaan')->insert([
            'no_surat' => $noSurat,
            'tanggal' => '2026-08-10',
            'jenis_permintaan' => 'PROJECT',
            'kode_project' => $project,
            'kode_area' => 'belu',
            'total_nominal' => '5000000',
            'status_surat' => 'Pending',
            'posisi_saat_ini' => 'FINANCE_PROJECT',
            'id_maker' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function coordinator_from_other_project_cannot_validate(): void
    {
        $this->createSpp('SCOPE-001', '40');

        $response = $this->actingAsUser('coord.x', 'KOORDINATOR_KEUANGAN', '38')->post('/spp/validasi', [
            'no_surat' => 'SCOPE-001',
            'aksi' => 'approve',
        ]);

        $response->assertSessionHas('error');

        $surat = DB::table('surat_permintaan')->where('no_surat', 'SCOPE-001')->first();
        $this->assertEquals('FINANCE_PROJECT', $surat->posisi_saat_ini);
    }

    /** @test */
    public function finance_from_matching_project_can_validate(): void
    {
        $this->createSpp('SCOPE-002', '40');

        $response = $this->actingAsUser('fp.y', 'FINANCE_PROJECT', '40')->post('/spp/validasi', [
            'no_surat' => 'SCOPE-002',
            'aksi' => 'approve',
        ]);

        $response->assertSessionHas('success');

        $surat = DB::table('surat_permintaan')->where('no_surat', 'SCOPE-002')->first();
        $this->assertEquals('PROJECT_MANAGER', $surat->posisi_saat_ini);
    }
}
