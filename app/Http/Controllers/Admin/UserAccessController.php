<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserAccessController extends Controller
{
    public const VALID_ROLES = [
        'ADMIN', 'MAKER', 'CHECKER', 'KASIR_PUSAT', 'DIREKTUR', 'MANAGER_KEUANGAN',
        'AREA_MANAGER', 'FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_PKP',
        'KOORDINATOR_KEUANGAN', 'KOORDINATOR_PK', 'KOORDINATOR_TC',
        'KOORDINATOR_DIKLAT', 'KOORDINATOR_KLINIK', 'KOORDINATOR_BATRA',
        'KOORDINATOR_BIDANG',
    ];

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $aksesRaw = $user->akses()->orderBy('id_access', 'asc')->get();

        $grouped = [];
        foreach ($aksesRaw as $a) {
            $key = $a->role.'|'.$a->kode_area.'|'.$a->jabatan;
            if (! isset($grouped[$key])) {
                $grouped[$key] = (object) [
                    'role' => $a->role,
                    'kode_area' => $a->kode_area,
                    'jabatan' => $a->jabatan,
                    'kode_project_list' => [],
                    'has_all' => false,
                    'has_null' => false,
                ];
            }

            if ($a->kode_project === 'all') {
                $grouped[$key]->has_all = true;
            } elseif ($a->kode_project === null || $a->kode_project === '') {
                $grouped[$key]->has_null = true;
            } else {
                $grouped[$key]->kode_project_list[] = $a->kode_project;
            }
        }

        $akses = collect();
        foreach ($grouped as $g) {
            $kode_project = null;
            if ($g->has_all) {
                $kode_project = 'all';
            } elseif (! empty($g->kode_project_list)) {
                $kode_project = implode(',', $g->kode_project_list);
            } elseif ($g->has_null) {
                $kode_project = null;
            }

            $akses->push((object) [
                'role' => $g->role,
                'kode_area' => $g->kode_area,
                'jabatan' => $g->jabatan,
                'kode_project' => $kode_project,
            ]);
        }

        $areas = Area::all();
        $projects = Project::all();

        return view('admin.user.access_edit', compact('user', 'akses', 'areas', 'projects'));
    }

    public function update(Request $request, $id)
    {
        Log::error('UserAccessController@update hit', [
            'user_id' => $id,
            'akses_present' => $request->has('akses'),
            'akses_count' => is_array($request->input('akses')) ? count($request->input('akses')) : null,
        ]);

        // Validasi dasar: akses harus array
        $request->validate([
            'akses' => 'required|array|min:1',
        ]);

        // Validasi setiap row secara manual — hanya row yang punya role/kode_area yang divalidasi ketat
        $errors = [];
        $validRows = [];
        $input = $request->input('akses');

        foreach ($input as $i => $row) {
            $role = $row['role'] ?? '';
            $kodeArea = $row['kode_area'] ?? '';
            $kodeProject = $row['kode_project'] ?? '';
            $jabatan = $row['jabatan'] ?? '';

            // Skip row kosong (extra row tidak diisi)
            if ($role === '' && $kodeArea === '' && $kodeProject === '' && $jabatan === '') {
                continue;
            }

            // Validasi row yang diisi
            if ($role === '') {
                $errors["akses.$i.role"] = 'Role wajib dipilih.';
            } elseif (! in_array($role, self::VALID_ROLES, true)) {
                $errors["akses.$i.role"] = 'Role tidak valid.';
            }

            if ($jabatan === '') {
                $errors["akses.$i.jabatan"] = 'Jabatan wajib diisi.';
            }

            $projectScopedRoles = ['FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN'];
            if (in_array($role, $projectScopedRoles, true)) {
                if ($kodeProject === '' || $kodeProject === null) {
                    $errors["akses.$i.kode_project"] = 'Kode project wajib dipilih untuk role project-scoped.';
                }
            } else {
                if ($kodeArea === '') {
                    $errors["akses.$i.kode_area"] = 'Area wajib dipilih.';
                }
            }

            if (in_array($role, $projectScopedRoles, true) && $kodeArea === '') {
                $row['kode_area'] = 'PUSAT';
            }

            if (! in_array($role, $projectScopedRoles, true) && ($kodeProject === '' || $kodeProject === 'all')) {
                $row['kode_project'] = null;
            }

            if (in_array($role, $projectScopedRoles, true) && $kodeProject === 'all') {
                $row['kode_project'] = 'all';
            }

            $validRows[] = $row;
        }

        if (! empty($errors)) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        if (empty($validRows)) {
            return redirect()->back()->withErrors(['akses' => 'Minimal satu baris akses harus diisi.'])->withInput();
        }

        $user = User::findOrFail($id);
        $idUser = $user->id_user ?? $user->id;

        try {
            $inserted = 0;

            DB::transaction(function () use ($validRows, $idUser, &$inserted) {
                // 1) hapus akses lama
                DB::table('user_access')->where('id_user', $idUser)->delete();

                // 2) insert akses baru
                foreach ($validRows as $row) {
                    $kodeProjectStr = $row['kode_project'] ?? null;

                    if ($kodeProjectStr === '' || $kodeProjectStr === null) {
                        $projectList = [null];
                    } elseif ($kodeProjectStr === 'all') {
                        $projectList = ['all'];
                    } else {
                        $projectList = array_filter(array_map('trim', explode(',', $kodeProjectStr)));
                        if (empty($projectList)) {
                            $projectList = ['all'];
                        }
                    }

                    foreach ($projectList as $pCode) {
                        DB::table('user_access')->insert([
                            'id_user' => $idUser,
                            'role' => $row['role'],
                            'kode_area' => $row['kode_area'],
                            'kode_project' => $pCode,
                            'jabatan' => $row['jabatan'],
                        ]);
                        $inserted++;
                    }
                }
            });

            Log::error('UserAccessController@update DB done', [
                'user_id' => $idUser,
                'inserted' => $inserted,
            ]);
        } catch (\Throwable $e) {
            Log::error('UserAccessController@update EXCEPTION', [
                'user_id' => $idUser,
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            throw $e;
        }

        Log::error('UserAccessController@update returning redirect', [
            'user_id' => $user->id_user ?? $user->id,
        ]);

        return redirect()->route('admin.user.access.edit', $user->id_user)
            ->with('success', 'Otorisasi user berhasil diperbarui!');
    }

    public function deleteRow(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string',
            'kode_area' => 'required|string',
            'kode_project' => 'nullable|string',
        ]);

        $user = User::findOrFail($id);
        $kodeProject = $request->input('kode_project');

        $idUser = $user->id_user ?? $user->id;

        $affected = DB::table('user_access')
            ->where('id_user', $idUser)
            ->where('role', $request->role)
            ->where('kode_area', $request->kode_area)
            ->when($kodeProject !== null && $kodeProject !== '', function ($q) use ($kodeProject) {
                $projects = array_filter(array_map('trim', explode(',', $kodeProject)));
                $q->whereIn('kode_project', $projects);
            })
            ->when($kodeProject === null || $kodeProject === '', function ($q) {
                $q->whereNull('kode_project');
            })
            ->delete();

        return redirect()->route('admin.user.access.edit', $user->id_user)
            ->with('success', $affected > 0 ? 'Akses role berhasil dihapus.' : 'Akses role tidak ditemukan/ tidak ada perubahan.');
    }

    public function clear(Request $request, $id)
    {
        $user = User::findOrFail($id);
        DB::table('user_access')->where('id_user', $user->id_user)->delete();

        return redirect()->route('admin.user.access.edit', $user->id_user)
            ->with('success', 'Otorisasi user berhasil dihapus.');
    }
}
