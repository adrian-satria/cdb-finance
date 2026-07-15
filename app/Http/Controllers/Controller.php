<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Controller
{
    // FUNGSI SAKTI OTOMATISASI AUDIT TRAIL
    public static function simpanLog($aksi, $deskripsi, $oldData = null)
    {
        DB::table('audit_trails')->insert([
            'username'       => Auth::user()->username ?? 'GUEST/SYSTEM',
            'role'           => session('role') ?? 'NO_ROLE',
            'aksi'           => $aksi,
            'deskripsi'      => $deskripsi,
            'payload_before' => $oldData ? json_encode($oldData) : null, // Merekam state data lama sebelum diubah
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(), // Melacak sidik jari browser/perangkat pengubah data
            'created_at'     => now()
        ]);
    }
}
