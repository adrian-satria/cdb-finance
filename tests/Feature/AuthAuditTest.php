<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $id = DB::table('users')->insertGetId([
            'name' => 'Audit User',
            'username' => 'audit.user',
            'password' => bcrypt('password'),
            'email' => 'audit@test.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->where('id', $id)->update(['id_user' => $id]);
        DB::table('user_access')->insert([
            'id_user' => $id,
            'role' => 'MAKER',
            'jabatan' => 'Maker',
            'kode_area' => 'belu',
        ]);
    }

    /** @test */
    public function successful_login_logs_audit(): void
    {
        $this->post('/login', [
            'username' => 'audit.user',
            'password' => 'password',
        ])->assertRedirect();

        $log = DB::table('audit_trails')
            ->where('aksi', 'LOGIN_SUCCESS')
            ->where('deskripsi', 'like', '%audit.user%')
            ->first();

        $this->assertNotNull($log);
    }

    /** @test */
    public function failed_login_logs_audit(): void
    {
        $this->post('/login', [
            'username' => 'audit.user',
            'password' => 'salah-password',
        ]);

        $log = DB::table('audit_trails')
            ->where('aksi', 'LOGIN_FAILED')
            ->where('deskripsi', 'like', '%audit.user%')
            ->first();

        $this->assertNotNull($log);
    }

    /** @test */
    public function logout_logs_audit(): void
    {
        $this->post('/login', [
            'username' => 'audit.user',
            'password' => 'password',
        ]);

        $this->post('/logout')->assertRedirect('/login');

        $log = DB::table('audit_trails')
            ->where('aksi', 'LOGOUT')
            ->where('deskripsi', 'like', '%audit.user%')
            ->first();

        $this->assertNotNull($log);
    }
}
