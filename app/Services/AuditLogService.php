<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditLogService
{
    /**
     * Log an audit trail entry.
     *
     * @param  string  $aksi  Action identifier (e.g. 'INSERT_SPP', 'APPROVAL_TRANSACTION')
     * @param  string  $deskripsi  Human-readable description
     * @param  mixed  $oldData  Previous state data (optional, will be JSON encoded)
     * @param  string|null  $username  Override username (default: current user)
     * @param  string|null  $role  Override role (default: current session role)
     */
    public static function log(
        string $aksi,
        string $deskripsi,
        mixed $oldData = null,
        ?string $username = null,
        ?string $role = null
    ): void {
        DB::table('audit_trails')->insert([
            'username' => $username ?? (Auth::check() ? Auth::user()->username : 'SYSTEM'),
            'role' => $role ?? (session('role') ?? 'SYSTEM'),
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'payload_before' => $oldData ? (is_string($oldData) ? $oldData : json_encode($oldData)) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log an activity entry.
     *
     * @param  string  $aktivitas  Activity type
     * @param  string  $deskripsi  Description
     * @param  array  $extra  Extra data
     */
    public static function logActivity(
        string $aktivitas,
        string $deskripsi,
        array $extra = []
    ): void {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();

        DB::table('activity_logs')->insert(array_merge([
            'user_id' => $user->id_user ?? $user->id,
            'username' => $user->username,
            'role' => session('role'),
            'aktivitas' => $aktivitas,
            'deskripsi' => $deskripsi,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'created_at' => now(),
        ], $extra));
    }

    /**
     * Log SPP workflow history entry.
     */
    public static function logSppHistory(
        string $noSurat,
        ?string $statusDari,
        string $statusKe,
        ?string $posisiDari,
        string $posisiKe,
        string $keterangan,
        mixed $payloadBefore = null
    ): void {
        DB::table('spp_history')->insert([
            'no_surat' => $noSurat,
            'status_dari' => $statusDari,
            'status_ke' => $statusKe,
            'posisi_dari' => $posisiDari,
            'posisi_ke' => $posisiKe,
            'aktor_username' => Auth::check() ? Auth::user()->username : 'SYSTEM',
            'aktor_role' => session('role') ?? 'SYSTEM',
            'keterangan' => $keterangan,
            'payload_before' => $payloadBefore ? (is_string($payloadBefore) ? $payloadBefore : json_encode($payloadBefore)) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Check if user has committed fraud by attempting unauthorized actions.
     * If detected, logs the attempt and returns false.
     */
    public static function logIllegalAccess(string $actionDescription): void
    {
        self::log('ILLEGAL_ACCESS', $actionDescription);
    }

    /**
     * Log user login activity.
     *
     * @param  bool  $success  Whether login was successful
     */
    public static function logLogin(string $username, bool $success): void
    {
        $aksi = $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED';
        $deskripsi = $success
            ? "User {$username} berhasil login."
            : "Percobaan login gagal untuk username {$username}.";

        self::log($aksi, $deskripsi);
    }

    /**
     * Log user logout activity.
     */
    public static function logLogout(): void
    {
        if (! Auth::check()) {
            return;
        }

        self::log('LOGOUT', 'User '.Auth::user()->username.' logout dari sistem.');
    }
}
