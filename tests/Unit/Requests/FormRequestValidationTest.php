<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\Spp\StoreSppRequest;
use App\Http\Requests\Spp\ValidateSppRequest;
use App\Http\Requests\Spp\DisburseSppRequest;
use App\Http\Requests\Admin\StoreBudgetRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateUserAccessRequest;
use App\Http\Requests\Profile\UploadSignatureRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class FormRequestValidationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Insert a test project for exists validation rules
        DB::table('project')->upsert(
            ['kode_project' => 'P01', 'nama_project' => 'Test Project'],
            'kode_project'
        );
    }

    // ==========================================
    // StoreSppRequest (8 tests)
    // ==========================================

    /** @test */
    public function store_spp_passes_with_valid_data()
    {
        $data = [
            'tanggal' => '2026-07-15',
            'kode_project' => 'P01',
            'items' => [
                ['kode_budget' => 'B001', 'jumlah' => '50000'],
            ],
        ];

        $request = new StoreSppRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function store_spp_fails_without_tanggal()
    {
        $data = ['kode_project' => 'P01', 'items' => [['kode_budget' => 'B001', 'jumlah' => '50000']]];
        $request = new StoreSppRequest();
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('tanggal'));
    }

    /** @test */
    public function store_spp_fails_without_items()
    {
        $data = ['tanggal' => '2026-07-15', 'kode_project' => 'P01'];
        $request = new StoreSppRequest();
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('items'));
    }

    /** @test */
    public function store_spp_fails_with_empty_items()
    {
        $data = ['tanggal' => '2026-07-15', 'kode_project' => 'P01', 'items' => []];
        $request = new StoreSppRequest();
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function store_spp_fails_with_invalid_project()
    {
        $data = ['tanggal' => '2026-07-15', 'kode_project' => 'INVALID', 'items' => [['kode_budget' => 'B001', 'jumlah' => '50000']]];
        $request = new StoreSppRequest();
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('kode_project'));
    }

    /** @test */
    public function store_spp_fails_with_item_jumlah_zero()
    {
        $data = ['tanggal' => '2026-07-15', 'kode_project' => 'P01', 'items' => [['kode_budget' => 'B001', 'jumlah' => '0']]];
        $request = new StoreSppRequest();
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function store_spp_passes_with_all_optional_fields()
    {
        $data = [
            'tanggal' => '2026-07-15',
            'kode_project' => 'P01',
            'jenis_permintaan' => 'SPP',
            'sumber_dana' => 'Bank BNI',
            'bank_tujuan' => 'BRI',
            'no_rekening_tujuan' => '1234567890',
            'nama_rekening_tujuan' => 'PT Test',
            'kode_area' => 'pusat',
            'items' => [
                ['kode_budget' => 'B001', 'jumlah' => '50000', 'keterangan' => 'Test item'],
            ],
        ];

        $request = new StoreSppRequest();
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function store_spp_fails_with_more_than_5_files()
    {
        $data = [
            'tanggal' => '2026-07-15',
            'kode_project' => 'P01',
            'items' => [['kode_budget' => 'B001', 'jumlah' => '50000']],
            'file_lampiran' => ['a', 'b', 'c', 'd', 'e', 'f'],
        ];
        $request = new StoreSppRequest();
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
    }

    // ==========================================
    // ValidateSppRequest (5 tests)
    // ==========================================

    /** @test */
    public function validate_spp_passes_with_valid_approve()
    {
        $request = new ValidateSppRequest();
        $validator = Validator::make(
            ['aksi' => 'approve'],
            $request->rules()
        );
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function validate_spp_passes_with_valid_revise()
    {
        $request = new ValidateSppRequest();
        $validator = Validator::make(
            ['aksi' => 'revise', 'alasan' => 'Perlu perbaikan'],
            $request->rules()
        );
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function validate_spp_passes_with_valid_reject()
    {
        $request = new ValidateSppRequest();
        $validator = Validator::make(
            ['aksi' => 'reject', 'alasan' => 'Dana tidak mencukupi'],
            $request->rules()
        );
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function validate_spp_fails_with_invalid_aksi()
    {
        $request = new ValidateSppRequest();
        $validator = Validator::make(['aksi' => 'delete'], $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('aksi'));
    }

    /** @test */
    public function validate_spp_fails_without_aksi()
    {
        $request = new ValidateSppRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertFalse($validator->passes());
    }

    // ==========================================
    // DisburseSppRequest (2 tests)
    // ==========================================

    /** @test */
    public function disburse_spp_fails_without_no_surat()
    {
        $request = new DisburseSppRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function disburse_spp_authorize_only_for_kasir_or_admin()
    {
        session(['role' => 'KASIR_PUSAT']);
        $request = new DisburseSppRequest();
        $this->assertTrue($request->authorize());

        session(['role' => 'ADMIN']);
        $request2 = new DisburseSppRequest();
        $this->assertTrue($request2->authorize());

        session(['role' => 'MAKER']);
        $request3 = new DisburseSppRequest();
        $this->assertFalse($request3->authorize());
    }

    // ==========================================
    // StoreBudgetRequest (3 tests)
    // ==========================================

    /** @test */
    public function store_budget_passes_with_valid_data()
    {
        $request = new StoreBudgetRequest();
        $validator = Validator::make(
            ['kode_project' => 'P01', 'kode_budget' => 'B001', 'nama_budget' => 'Test Budget', 'alokasi_dana' => '100000'],
            $request->rules()
        );
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function store_budget_fails_without_required_fields()
    {
        $request = new StoreBudgetRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('kode_project'));
        $this->assertTrue($validator->errors()->has('kode_budget'));
        $this->assertTrue($validator->errors()->has('nama_budget'));
        $this->assertTrue($validator->errors()->has('alokasi_dana'));
    }

    /** @test */
    public function store_budget_fails_with_negative_alokasi()
    {
        $request = new StoreBudgetRequest();
        $validator = Validator::make(
            ['kode_project' => 'P01', 'kode_budget' => 'B001', 'nama_budget' => 'Test', 'alokasi_dana' => '-100'],
            $request->rules()
        );
        $this->assertFalse($validator->passes());
    }

    // ==========================================
    // StoreUserRequest (3 tests)
    // ==========================================

    /** @test */
    public function store_user_passes_with_valid_data()
    {
        $request = new StoreUserRequest();
        $validator = Validator::make(
            ['nama' => 'Test User', 'username' => 'testuser', 'password' => 'pass123'],
            $request->rules()
        );
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function store_user_fails_without_required_fields()
    {
        $request = new StoreUserRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('nama'));
        $this->assertTrue($validator->errors()->has('username'));
        $this->assertTrue($validator->errors()->has('password'));
    }

    /** @test */
    public function store_user_fails_with_short_password()
    {
        $request = new StoreUserRequest();
        $validator = Validator::make(
            ['nama' => 'Test', 'username' => 'test', 'password' => '12'],
            $request->rules()
        );
        $this->assertFalse($validator->passes());
    }

    // ==========================================
    // UpdateUserRequest (2 tests)
    // ==========================================

    /** @test */
    public function update_user_passes_with_valid_data()
    {
        $request = new UpdateUserRequest();
        $validator = Validator::make(
            ['nama' => 'Updated Name', 'username' => 'updateduser'],
            $request->rules()
        );
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function update_user_allows_optional_password()
    {
        $request = new UpdateUserRequest();
        $validator = Validator::make(
            ['nama' => 'Test', 'username' => 'test'],
            $request->rules()
        );
        $this->assertTrue($validator->passes());
    }

    // ==========================================
    // UpdateUserAccessRequest (2 tests)
    // ==========================================

    /** @test */
    public function update_user_access_passes_with_valid_data()
    {
        $request = new UpdateUserAccessRequest();
        $validator = Validator::make(
            [
                'akses' => [
                    ['role' => 'ADMIN', 'jabatan' => 'Administrator', 'kode_area' => 'pusat'],
                ],
            ],
            $request->rules()
        );
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function update_user_access_fails_without_akses()
    {
        $request = new UpdateUserAccessRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('akses'));
    }

    // ==========================================
    // UploadSignatureRequest (2 tests)
    // ==========================================

    /** @test */
    public function upload_signature_fails_without_file()
    {
        $request = new UploadSignatureRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('signature'));
    }

    /** @test */
    public function upload_signature_accepts_only_image_types()
    {
        $request = new UploadSignatureRequest();
        $rules = $request->rules();
        $this->assertEquals('required|file|mimes:png,jpg,jpeg|max:2048', $rules['signature']);
    }

    // ==========================================
    // ChangePasswordRequest (3 tests)
    // ==========================================

    /** @test */
    public function change_password_passes_with_valid_data()
    {
        $request = new ChangePasswordRequest();
        $validator = Validator::make(
            ['current_password' => 'oldpass', 'new_password' => 'newpassword123', 'new_password_confirmation' => 'newpassword123'],
            $request->rules()
        );
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function change_password_fails_without_confirmation()
    {
        $request = new ChangePasswordRequest();
        $validator = Validator::make(
            ['current_password' => 'old', 'new_password' => 'newpassword123'],
            $request->rules()
        );
        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function change_password_fails_with_short_password()
    {
        $request = new ChangePasswordRequest();
        $validator = Validator::make(
            ['current_password' => 'old', 'new_password' => 'short', 'new_password_confirmation' => 'short'],
            $request->rules()
        );
        $this->assertFalse($validator->passes());
    }
}
