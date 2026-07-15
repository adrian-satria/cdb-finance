<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        $user = Auth::user();
        $dir = 'private/signatures';

        // Hapus signature lama bila ada (best-effort)
        $old = DB::table('users')->where('id_user', $user->id_user)->value('signature_path');
        if ($old) {
            $oldPath = storage_path('app/' . $dir . '/' . $old);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $ext = $request->file('signature')->getClientOriginalExtension();
        $newName = 'sig_' . $user->id_user . '_' . time() . '_' . uniqid() . '.' . $ext;

        $request->file('signature')->move(storage_path('app/' . $dir), $newName);

        DB::table('users')->where('id_user', $user->id_user)->update([
            'signature_path' => $newName,
            'updated_at' => now(),
        ]);

        // Catat audit
        DB::table('audit_trails')->insert([
            'username' => Auth::user()->username ?? 'GUEST/SYSTEM',
            'role' => session('role') ?? 'NO_ROLE',
            'aksi' => 'UPLOAD_SIGNATURE',
            'deskripsi' => 'User mengupdate tanda tangan digital.',
            'payload_before' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('profile.index')->with('success', 'Tanda tangan berhasil diupload.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();
        }

        DB::table('users')->where('id_user', $user->id_user)->update([
            'password' => Hash::make($request->new_password),
            'updated_at' => now(),
        ]);

        DB::table('audit_trails')->insert([
            'username' => Auth::user()->username ?? 'GUEST/SYSTEM',
            'role' => session('role') ?? 'NO_ROLE',
            'aksi' => 'CHANGE_PASSWORD',
            'deskripsi' => 'User mengganti password.',
            'payload_before' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        // Regenerate session agar sesi aman
        $request->session()->regenerateToken();

        return redirect()->route('profile.index')->with('success', 'Password berhasil diganti.');
    }
}

