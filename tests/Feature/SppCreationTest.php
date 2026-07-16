<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

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
        $this->actingAs(\App\Models\User::find($userId));
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
        $response->assertSessionHas('error');
    }

    /** @test */
    public function user_can_create_spp_with_file_attachments(): void
    {
        $this->seedBudget();

        $file = UploadedFile::fake()->create('lampiran.pdf', 100);

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
}
