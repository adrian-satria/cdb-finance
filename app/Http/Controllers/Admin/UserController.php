<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(20);
        return view('admin.user.index', compact('users'));
    }

    public function create()
    {
        $areas = Area::all(); // Mengambil data unit kerja/wilayah tugas
        return view('admin.user.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:4',
        ]);

        $user = User::create([
            'name' => $request->nama,
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email' => $request->username . '@cdbfinance.test',
        ]);

        // Set id_user = id for compatibility with legacy code
        $user->id_user = $user->id;
        $user->save();

        return redirect()->route('admin.user.index')->with('success', 'User baru berhasil didaftarkan!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id_user . ',id_user',
        ]);

        $user->nama = $request->nama;
        $user->username = $request->username;

        // Jika password diisi baru, maka update. Jika kosong, pakai password lama.
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus dari sistem!');
    }
}