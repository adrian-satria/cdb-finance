<?php

namespace Tests\Unit\Services;

use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a test user session
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test Admin',
            'username' => 'audit_admin',
            'email' => 'audit@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $userId)->update(['id_user' => $userId]);
        session(['role' => 'ADMIN']);
        Auth::loginUsingId($userId);
    }

    // ==========================================
    // Test Group 1: Audit Trails (4 tests)
    // ==========================================

    /** @test */
    public function it_logs_audit_trail_entry()
    {
        AuditLogService::log('TEST_ACTION', 'Test description');

        $entry = DB::table('audit_trails')->where('aksi', 'TEST_ACTION')->first();
        $this->assertNotNull($entry);
        $this->assertEquals('audit_admin', $entry->username);
        $this->assertEquals('ADMIN', $entry->role);
        $this->assertEquals('Test description', $entry->deskripsi);
    }

    /** @test */
    public function it_logs_with_old_data()
    {
        $oldData = ['status' => 'Pending', 'nominal' => '50000'];
        AuditLogService::log('UPDATE_TEST', 'Updated something', $oldData);

        $entry = DB::table('audit_trails')->where('aksi', 'UPDATE_TEST')->first();
        $this->assertNotNull($entry);
        $this->assertStringContainsString('Pending', $entry->payload_before);
        $this->assertStringContainsString('50000', $entry->payload_before);
    }

    /** @test */
    public function it_logs_with_custom_username()
    {
        AuditLogService::log('CUSTOM_USER', 'Custom user test', null, 'superadmin', 'SUPER_ADMIN');

        $entry = DB::table('audit_trails')->where('aksi', 'CUSTOM_USER')->first();
        $this->assertEquals('superadmin', $entry->username);
        $this->assertEquals('SUPER_ADMIN', $entry->role);
    }

    /** @test */
    public function it_logs_illegal_access()
    {
        AuditLogService::logIllegalAccess('User mencoba akses tanpa izin');

        $entry = DB::table('audit_trails')->where('aksi', 'ILLEGAL_ACCESS')->first();
        $this->assertNotNull($entry);
        $this->assertStringContainsString('tanpa izin', $entry->deskripsi);
    }

    // ==========================================
    // Test Group 2: Activity Logs (3 tests)
    // ==========================================

    /** @test */
    public function it_logs_activity()
    {
        AuditLogService::logActivity('CREATE_SPP', 'User membuat SPP baru');

        $entry = DB::table('activity_logs')->where('aktivitas', 'CREATE_SPP')->first();
        $this->assertNotNull($entry);
        $this->assertEquals('audit_admin', $entry->username);
        $this->assertEquals('ADMIN', $entry->role);
    }

    /** @test */
    public function it_logs_activity_without_extra_data()
    {
        AuditLogService::logActivity('LOGIN', 'User login');

        $entry = DB::table('activity_logs')->where('aktivitas', 'LOGIN')->first();
        $this->assertNotNull($entry);
        $this->assertEquals('User login', $entry->deskripsi);
    }

    /** @test */
    public function it_does_not_log_activity_when_not_authenticated()
    {
        Auth::logout();
        AuditLogService::logActivity('TEST', 'Should not appear');

        $count = DB::table('activity_logs')->where('aktivitas', 'TEST')->count();
        $this->assertEquals(0, $count);
    }

    // ==========================================
    // Test Group 3: SPP History (3 tests)
    // ==========================================

    /** @test */
    public function it_logs_spp_history()
    {
        AuditLogService::logSppHistory(
            noSurat: 'SPP-TEST-001',
            statusDari: 'Pending',
            statusKe: 'Approved',
            posisiDari: 'MANAGER_KEUANGAN',
            posisiKe: 'KASIR_PUSAT',
            keterangan: 'Disetujui oleh Manager Keuangan'
        );

        $entry = DB::table('spp_history')->where('no_surat', 'SPP-TEST-001')->first();
        $this->assertNotNull($entry);
        $this->assertEquals('Pending', $entry->status_dari);
        $this->assertEquals('Approved', $entry->status_ke);
        $this->assertEquals('MANAGER_KEUANGAN', $entry->posisi_dari);
        $this->assertEquals('KASIR_PUSAT', $entry->posisi_ke);
    }

    /** @test */
    public function it_logs_spp_history_with_payload()
    {
        $payload = ['total_nominal' => '100000', 'status' => 'Pending'];
        AuditLogService::logSppHistory(
            noSurat: 'SPP-TEST-002',
            statusDari: null,
            statusKe: 'Pending',
            posisiDari: null,
            posisiKe: 'MANAGER_KEUANGAN',
            keterangan: 'SPP baru dibuat',
            payloadBefore: $payload
        );

        $entry = DB::table('spp_history')->where('no_surat', 'SPP-TEST-002')->first();
        $this->assertNotNull($entry);
        $this->assertNull($entry->status_dari);
        $this->assertStringContainsString('100000', $entry->payload_before);
    }

    /** @test */
    public function it_logs_spp_history_with_revision()
    {
        AuditLogService::logSppHistory(
            noSurat: 'SPP-TEST-003',
            statusDari: 'Pending',
            statusKe: 'Revisi',
            posisiDari: 'MANAGER_KEUANGAN',
            posisiKe: 'MAKER',
            keterangan: 'Perlu revisi: dokumen tidak lengkap'
        );

        $entry = DB::table('spp_history')->where('no_surat', 'SPP-TEST-003')->first();
        $this->assertEquals('Revisi', $entry->status_ke);
        $this->assertEquals('MAKER', $entry->posisi_ke);
    }

    // ==========================================
    // Test Group 4: Login/Logout (3 tests)
    // ==========================================

    /** @test */
    public function it_logs_successful_login()
    {
        AuditLogService::logLogin('audit_admin', true);

        $entry = DB::table('audit_trails')->where('aksi', 'LOGIN_SUCCESS')->first();
        $this->assertNotNull($entry);
        $this->assertStringContainsString('berhasil login', $entry->deskripsi);
    }

    /** @test */
    public function it_logs_failed_login()
    {
        AuditLogService::logLogin('unknown_user', false);

        $entry = DB::table('audit_trails')->where('aksi', 'LOGIN_FAILED')->first();
        $this->assertNotNull($entry);
        $this->assertStringContainsString('gagal', $entry->deskripsi);
    }

    /** @test */
    public function it_logs_logout()
    {
        AuditLogService::logLogout();

        $entry = DB::table('audit_trails')->where('aksi', 'LOGOUT')->first();
        $this->assertNotNull($entry);
        $this->assertStringContainsString('audit_admin', $entry->deskripsi);
    }
}
