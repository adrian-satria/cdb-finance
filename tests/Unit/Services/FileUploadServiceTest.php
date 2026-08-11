<?php

namespace Tests\Unit\Services;

use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FileUploadServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected FileUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FileUploadService;
    }

    // ==========================================
    // Test Group 1: File Type & Size Validation (6 tests)
    // ==========================================

    /** @test */
    public function it_validates_allowed_file_types()
    {
        $this->assertTrue($this->service->isValidFileType(UploadedFile::fake()->create('doc.pdf')));
        $this->assertTrue($this->service->isValidFileType(UploadedFile::fake()->image('photo.jpg')));
        $this->assertTrue($this->service->isValidFileType(UploadedFile::fake()->image('photo.jpeg')));
        $this->assertTrue($this->service->isValidFileType(UploadedFile::fake()->image('photo.png')));
    }

    /** @test */
    public function it_rejects_invalid_file_types()
    {
        $this->assertFalse($this->service->isValidFileType(UploadedFile::fake()->create('doc.exe')));
        $this->assertFalse($this->service->isValidFileType(UploadedFile::fake()->create('script.php')));
        $this->assertFalse($this->service->isValidFileType(UploadedFile::fake()->create('data.csv')));
    }

    /** @test */
    public function it_validates_file_size_within_limit()
    {
        $file = UploadedFile::fake()->create('doc.pdf', 100); // 100KB
        $this->assertTrue($this->service->isValidFileSize($file, 5120));
    }

    /** @test */
    public function it_rejects_file_size_exceeding_limit()
    {
        $file = UploadedFile::fake()->create('doc.pdf', 6000); // 6000KB = ~6MB
        $this->assertFalse($this->service->isValidFileSize($file, 5120));
    }

    /** @test */
    public function it_validates_exact_size_limit()
    {
        $file = UploadedFile::fake()->create('doc.pdf', 5120);
        $this->assertTrue($this->service->isValidFileSize($file, 5120));
    }

    /** @test */
    public function it_accepts_custom_allowed_types()
    {
        $excel = UploadedFile::fake()->create('data.xlsx');
        $this->assertFalse($this->service->isValidFileType($excel));
        $this->assertTrue($this->service->isValidFileType($excel, ['xlsx', 'xls']));
    }

    // ==========================================
    // Test Group 2: File Access Authorization (6 tests)
    // ==========================================

    /** @test */
    public function it_allows_admin_to_access_any_file()
    {
        $record = (object) ['kode_area' => 'alor', 'kode_project' => '38'];
        $this->assertTrue($this->service->canAccessFile('ADMIN', null, null, $record));
    }

    /** @test */
    public function it_allows_kasir_pusat_to_access_any_file()
    {
        $record = (object) ['kode_area' => 'belu', 'kode_project' => '40'];
        $this->assertTrue($this->service->canAccessFile('KASIR_PUSAT', null, null, $record));
    }

    /** @test */
    public function it_allows_area_staff_to_access_their_area_files()
    {
        $record = (object) ['kode_area' => 'alor', 'kode_project' => '38'];
        $this->assertTrue($this->service->canAccessFile('MAKER', 'alor', null, $record));
    }

    /** @test */
    public function it_denies_area_staff_from_other_areas()
    {
        $record = (object) ['kode_area' => 'belu', 'kode_project' => '40'];
        $this->assertFalse($this->service->canAccessFile('MAKER', 'alor', null, $record));
    }

    /** @test */
    public function it_allows_project_role_to_access_their_project_files()
    {
        $record = (object) ['kode_area' => 'pusat', 'kode_project' => '38'];
        $this->assertTrue($this->service->canAccessFile('FINANCE_PROJECT', null, '38', $record));
    }

    /** @test */
    public function it_denies_project_role_from_other_projects()
    {
        $record = (object) ['kode_area' => 'pusat', 'kode_project' => '40'];
        $this->assertFalse($this->service->canAccessFile('FINANCE_PROJECT', null, '38', $record));
    }

    // ==========================================
    // Test Group 3: SPP File Upload (2 tests)
    // ==========================================

    /** @test */
    public function it_creates_file_records_in_database_on_upload()
    {
        $files = [
            UploadedFile::fake()->createWithContent('lampiran1.pdf', "%PDF-1.4\n%fakepdf"),
            UploadedFile::fake()->createWithContent('lampiran2.jpg', "\xFF\xD8\xFF\xE0JFIF"),
        ];

        $records = $this->service->uploadSppAttachments($files, 'SPP-TEST-001', 'MAKER');

        $this->assertCount(2, $records);
        $this->assertEquals('MAKER', $records[0]['kategori']);
        $this->assertEquals('MAKER', $records[1]['kategori']);

        // Verify database records
        $dbRecords = DB::table('surat_permintaan_files')
            ->where('no_surat', 'SPP-TEST-001')
            ->get();
        $this->assertCount(2, $dbRecords);
    }

    /** @test */
    public function it_handles_empty_file_array()
    {
        $records = $this->service->uploadSppAttachments([], 'SPP-TEST-001', 'MAKER');
        $this->assertCount(0, $records);
    }

    // ==========================================
    // Test Group 4: Signature Operations (2 tests)
    // ==========================================

    /** @test */
    public function it_uploads_signature_and_updates_user()
    {
        // Create test user
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $userId)->update(['id_user' => $userId]);

        $file = UploadedFile::fake()->image('signature.png', 50);

        $filename = $this->service->uploadSignature($file, $userId);

        $this->assertStringContainsString("sig_{$userId}_", $filename);
        $this->assertStringEndsWith('.png', $filename);

        // Verify user record updated
        $user = DB::table('users')->where('id_user', $userId)->first();
        $this->assertEquals($filename, $user->signature_path);
    }

    /** @test */
    public function it_deletes_old_signature_when_uploading_new()
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'username' => 'testuser2',
            'email' => 'test2@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $userId)->update(['id_user' => $userId]);

        // Set old signature
        DB::table('users')->where('id_user', $userId)->update([
            'signature_path' => 'sig_old_test.png',
        ]);

        $file = UploadedFile::fake()->image('new_signature.png', 50);
        $newFilename = $this->service->uploadSignature($file, $userId);

        $user = DB::table('users')->where('id_user', $userId)->first();
        $this->assertEquals($newFilename, $user->signature_path);
        $this->assertNotEquals('sig_old_test.png', $user->signature_path);
    }

    // ==========================================
    // Test Group 5: File Deletion (2 tests)
    // ==========================================

    /** @test */
    public function it_deletes_spp_file_record()
    {
        // Insert test file record
        DB::table('surat_permintaan_files')->insert([
            'no_surat' => 'TEST-DELETE',
            'nama_file' => 'test_delete.pdf',
            'kategori' => 'MAKER',
            'tipe_file' => 'pdf',
        ]);

        $result = $this->service->deleteSppFile('test_delete.pdf');
        $this->assertTrue($result);

        // Verify record removed
        $record = DB::table('surat_permintaan_files')
            ->where('nama_file', 'test_delete.pdf')
            ->first();
        $this->assertNull($record);
    }

    /** @test */
    public function it_handles_deleting_nonexistent_file()
    {
        $result = $this->service->deleteSppFile('nonexistent_file.pdf');
        $this->assertTrue($result);
    }

    // ==========================================
    // Test Group 6: Download Authorization (4 tests)
    // ==========================================

    /** @test */
    public function it_returns_null_when_file_not_found()
    {
        $result = $this->service->getSppFilePath('nonexistent.pdf');
        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_for_unauthorized_download()
    {
        $result = $this->service->downloadSppFile(
            'any_file.pdf',
            'MAKER',
            'alor',
            null
        );
        $this->assertNull($result);
    }

    /** @test */
    public function it_generates_unique_ids()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateUniqueId');
        $method->setAccessible(true);

        $id1 = $method->invoke($this->service);
        $id2 = $method->invoke($this->service);

        $this->assertNotEquals($id1, $id2);
        $this->assertEquals(16, strlen($id1)); // bin2hex(random_bytes(8)) = 16 chars
    }
}
