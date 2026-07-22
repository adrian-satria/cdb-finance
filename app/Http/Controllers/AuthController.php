<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $akses = $user->akses;

            if ($akses->isEmpty()) {
                return back()->with('error', 'Akun Anda tidak memiliki akses peran. Hubungi administrator.');
            }

            $role = $akses->first();
            $this->setSession($role);

            session([
                'user_roles' => $akses->toArray(),
                'last_activity' => time(),
            ]);

            if (session('role') == 'ADMIN') {
                return redirect()->intended('/admin/user');
            }

            return redirect()->intended('/spp/tambah');
        }

        return back()->with('error', 'Username atau Password salah!');
    }

    public function switchRole(Request $request)
    {
        $request->validate(['role_id' => 'required|integer']);

        $validAccess = DB::table('user_access')
            ->where('id_user', Auth::id())
            ->where('id_access', $request->role_id)
            ->first();

        if (! $validAccess) {
            return back()->with('error', 'Role tidak valid.');
        }

        session([
            'jabatan' => $validAccess->jabatan,
            'role' => $validAccess->role,
            'kode_area' => $validAccess->kode_area,
            'kode_project' => $validAccess->kode_project,
            'last_activity' => time(),
        ]);

        AuditLogService::log('SWITCH_ROLE', "User switch role ke {$validAccess->role} ({$validAccess->kode_area})");

        return redirect()->back();
    }

    public function setPeran(Request $request)
    {
        // PENGUATAN: Validasi input dan verifikasi kepemilikan peran di server-side
        $request->validate([
            'role' => 'required|string',
            'kode_area' => 'required|string',
            'kode_project' => 'nullable|string',
        ]);

        $user = Auth::user();

        // DB Anda: users PK = id_user
        $idUser = $user->id_user;

        $validAccess = DB::table('user_access')
            ->where('id_user', $idUser)
            ->where('role', $request->role)
            ->where('kode_area', $request->kode_area)
            ->when($request->kode_project !== null && $request->kode_project !== '', function ($query) use ($request) {
                $query->where('kode_project', $request->kode_project);
            })
            ->when($request->kode_project === null || $request->kode_project === '', function ($query) {
                $query->whereNull('kode_project');
            })
            ->first();

        if (! $validAccess) {
            AuditLogService::log('FRAUD_ATTEMPT', "User mencoba memalsukan peran/area: {$request->role} di area {$request->kode_area}");
            Auth::logout();

            return redirect('/login')->with('error', 'Akses Ilegal: Peran tidak terdaftar!');
        }

        // Defensive: pastikan $validAccess terdefinisi (mencegah intelephense false-positive)
        $validAccess = $validAccess ?? null;
        if (! $validAccess) {
            Auth::logout();

            return redirect('/login')->with('error', 'Akses Ilegal: Peran tidak terdaftar!');
        }

        session([
            'jabatan' => $validAccess->jabatan,
            'role' => $validAccess->role,
            'kode_area' => $validAccess->kode_area,
            'kode_project' => $validAccess->kode_project,
            'user_roles' => Auth::user()->akses->toArray(),
            'last_activity' => time(),
        ]);

        if (session('role') == 'ADMIN') {
            return redirect('/admin/user');
        }

        return redirect('/spp/tambah');
    }

    private function setSession($role)
    {
        // Menyimpan data sesi secara spesifik dan terpisah demi keamanan otorisasi sidebar
        session([
            'jabatan' => $role->jabatan,   // Menyimpan nama jabatan asli organisasi
            'role' => $role->role,      // <--- BARU: Menyimpan ENUM role untuk otorisasi
            'kode_area' => $role->kode_area,
            'kode_project' => $role->kode_project,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
