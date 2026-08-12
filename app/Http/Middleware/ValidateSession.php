<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use App\Models\UserAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ValidateSession
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (! session()->has('role')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            DB::table('audit_trails')->insert([
                'username' => Auth::user()->username ?? 'UNKNOWN',
                'role' => 'NO_ROLE',
                'aksi' => 'SESSION_INVALID',
                'deskripsi' => 'Session tidak memiliki role, user di-logout paksa',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return redirect('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }

        $hasAccess = UserAccess::where('id_user', Auth::id())
            ->where('role', session('role'))
            ->exists();

        if (! $hasAccess) {
            DB::table('audit_trails')->insert([
                'username' => Auth::user()->username,
                'role' => session('role'),
                'aksi' => 'PRIVILEGE_ESCALATION_ATTEMPT',
                'deskripsi' => 'Role di session tidak valid di database, kemungkinan privilege escalation',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', 'Hak akses Anda telah dicabut. Silakan hubungi administrator.');
        }

        $lastActivity = session('last_activity', time());
        $inactiveTime = time() - $lastActivity;

        // S3 — timeout dari SystemSetting (menit), default 120 menit
        $timeoutSeconds = (int) SystemSetting::getValue('session_timeout', 120) * 60;

        if ($inactiveTime > $timeoutSeconds) {
            DB::table('audit_trails')->insert([
                'username' => Auth::user()->username,
                'role' => session('role'),
                'aksi' => 'SESSION_TIMEOUT',
                'deskripsi' => "Session timeout setelah {$inactiveTime} detik tidak aktif",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', 'Session Anda telah habis karena tidak aktif. Silakan login kembali.');
        }

        session(['last_activity' => time()]);

        return $next($request);
    }
}
