<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // PENGUATAN: Pastikan user sudah login & session role-nya valid terdaftar di whitelist parameter
        if (! Auth::check() || ! session()->has('role') || ! in_array(session('role'), $roles)) {

            Log::warning('Unauthorized access attempt', [
                'user' => Auth::user()->username ?? 'GUEST',
                'url' => $request->fullUrl(),
            ]);

            // Catat log percobaan fraud / penyusupan ilegal jika ada yang coba tembak url
            DB::table('audit_trails')->insert([
                'username' => Auth::user()->username ?? 'GUEST_ANONYMOUS',
                'role' => session('role') ?? 'NO_ROLE',
                'aksi' => 'ILLEGAL_PRIVILEGE_ACCESS',
                'deskripsi' => 'Percobaan mengakses endpoint terlarang: '.$request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            abort(403, 'AKSES DITOLAK: Anda tidak memiliki otoritas formal untuk mengeksekusi halaman ini.');
        }

        return $next($request);
    }
}
