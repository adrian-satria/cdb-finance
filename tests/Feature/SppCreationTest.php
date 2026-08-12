<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SppCreationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestUser();
        $this->seedProject();
    }

    private function seedTestUser(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test Maker',
            'username' => 'testmaker',
            'password' => bcrypt('password'),
            'email' => 'maker@test.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $userId)->update(['id_user' => $userId]);
        DB::table('user_access')->insert([
            'id_user' => $userId,
            'role' => 'MAKER',
            'jabatan' => 'Test Maker',
            'kode_area' => 'pusat',
        ]);
        session(['role' => 'MAKER', 'kode_area' => 'pusat']);
        $this->actingAs(User::find($userId));
    }

    private function seedProject(): void
    {
        DB::table('project')->upsert(
            ['kode_project' => 'FT01', 'nama_project' => 'Feature Test Project'],
            'kode_project'
        );
    }

    private function seedBudget(): void
    {
        DB::table('master_budget')->insert([
            'kode_project' => 'FT01',
            'kode_budget' => 'FTB-001',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '50000000',
            'terserap' => '0',
        ]);

        DB::table('budget_area')->insert([
            'kode_project' => 'FT01',
            'kode_area' => 'pusat',
            'kode_budget' => 'FTB-001',
            'nama_budget' => 'Test Budget',
            'alokasi_dana' => '50000000',
            'terserap' => '0',
            'terserap_sementara' => '0',
        ]);
    }

    /** @test */
    public function user_can_view_spp_create_form(): void
    {
        $response = $this->get('/spp/tambah');
        $response->assertStatus(200);
        $response->assertSee('Input SPP');
    }

    /** @test */
    public function user_can_create_spp_with_valid_data(): void
    {
        $this->seedBudget();

        $response = $this->post('/spp/simpan', [
            'tanggal' => '2026-07-15',
            'kode_project' => 'FT01',
            'kode_area' => 'pusat',
            'items' => [
                ['kode_budget' => 'FTB-001', 'jumlah' => '500000', 'keterangan' => 'Test item'],
            ],
        ]);

        $response->assertRedirect('/spp');
        $response->assertSessionHas('success');

        $spp = DB::table('surat_permintaan')->where('kode_project', 'FT01')->first();
        $this->assertNotNull($spp);
        $this->assertEquals('Pending', $spp->status_surat);
    }

    /** @test */
    public function user_cannot_create_spp_without_items(): void
    {
        $response = $this->post('/spp/simpan', [
            'tanggal' => '2026-07-15',
            'kode_project' => 'FT01',
        ]);

        $response->assertSessionHasErrors('items');
    }

    /** @test */
    public function user_cannot_create_spp_with_invalid_project(): void
    {
        $response = $this->post('/spp/simpan', [
            'tanggal' => '2026-07-15',
            'kode_project' => 'INVALID',
            'items' => [['kode_budget' => 'B001', 'jumlah' => '50000']],
        ]);

        $response->assertSessionHasErrors('kode_project');
    }

    /** @test */
    public function user_cannot_create_spp_exceeding_budget(): void
    {
        $this->seedBudget();

        $response = $this->post('/spp/simpan', [
            'tanggal' => '2026-07-15',
            'kode_project' => 'FT01',
            'kode_area' => 'pusat',
            'items' => [
                ['kode_budget' => 'FTB-001', 'jumlah' => '999999999', 'keterangan' => 'Over budget'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');
    }

    /** @test */
    public function user_can_create_spp_with_file_attachments(): void
    {
        $this->seedBudget();

        $file = UploadedFile::fake()->createWithContent('lampiran.pdf', '%PDF-1.4 fake pdf content');

        $response = $this->post('/spp/simpan', [
            'tanggal' => '2026-07-15',
            'kode_project' => 'FT01',
            'kode_area' => 'pusat',
            'items' => [
                ['kode_budget' => 'FTB-001', 'jumlah' => '500000', 'keterangan' => 'Test'],
            ],
            'file_lampiran' => [$file],
        ]);

        $response->assertRedirect('/spp');
        $response->assertSessionHas('success');
    }

    /** @test */
    public function staff_area_cannot_create_spp_for_other_area(): void
    {
        $response = $this->post('/spp/simpan', [
            'tanggal' => '2026-07-15',
            'kode_project' => 'FT01',
            'kode_area' => 'belu',
            'items' => [
                ['kode_budget' => 'FTB-001', 'jumlah' => '500000', 'keterangan' => 'Test'],
            ],
        ]);

        $response->assertSessionHasErrors('kode_area');
    }

    /** @test */
    public function project_scoped_user_cannot_create_spp_for_other_project(): void
    {
        $projectUserId = DB::table('users')->insertGetId([
            'name' => 'Test Finance',
            'username' => 'financetest',
            'password' => bcrypt('password'),
            'email' => 'finance@test.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $projectUserId)->update(['id_user' => $projectUserId]);
        DB::table('user_access')->insert([
            'id_user' => $projectUserId,
            'role' => 'FINANCE_PROJECT',
            'jabatan' => 'Test Finance',
            'kode_area' => 'pusat',
            'kode_project' => 'FT01',
        ]);
        DB::table('project')->upsert(
            ['kode_project' => 'FT02', 'nama_project' => 'Other Project'],
            'kode_project'
        );
        DB::table('project_area')->upsert(
            ['kode_project' => 'FT01', 'kode_area' => 'pusat'],
            ['kode_project', 'kode_area']
        );

        $this->actingAs(User::find($projectUserId))->withSession([
            'role' => 'FINANCE_PROJECT',
            'kode_area' => 'pusat',
            'kode_project' => 'FT01',
        ]);

        $response = $this->post('/spp/simpan', [
            'tanggal' => '2026-07-15',
            'kode_project' => 'FT02',
            'kode_area' => 'pusat',
            'items' => [
                ['kode_budget' => 'FTB-001', 'jumlah' => '500000', 'keterangan' => 'Test'],
            ],
        ]);

        $response->assertSessionHasErrors('kode_project');
    }
}
