@extends('layouts.app')

@section('title', 'Kelola User | B-SMART')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary m-0"><i class="fa-solid fa-users-gear me-2"></i>Manajemen Data Pengguna</h4>
        <a href="/admin/user/create" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah User Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: var(--radius-lg);">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle border-light">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="45%">Nama Lengkap</th>
                            <th width="20%">Username</th>
                            <th width="35%">Jabatan / Akses</th>
                            <th width="15%" class="text-center">Aksi</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $u)
                        <tr>
                            <td class="text-center fw-semibold text-secondary">{{ $users->firstItem() + $index }}</td>
                            <td class="fw-bold text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size:12px;">
                                        {{ strtoupper(substr($u->nama, 0, 2)) }}
                                    </div>
                                    {{ $u->nama }}
                                </div>
                            </td>
                            <td><span class="badge bg-light text-secondary border px-2.5 py-1.5 fs-7">{{ $u->username }}</span></td>
                            <td>
                                @php
                                    $akses = $u->akses ?? collect();
                                    $items = $akses->map(function($a){
                                        $jabatan = $a->jabatan ?? '-';
                                        $kodeArea = $a->kode_area ?? '-';
                                        $role = $a->role ?? '-';
                                        return "{$jabatan} ({$role}) - {$kodeArea}";
                                    })->filter();
                                @endphp
                                @if($items->count() > 0)
                                    @foreach($items as $line)
                                        <div class="small text-dark" style="line-height:1.35;">• {{ $line }}</div>
                                    @endforeach
                                @else
                                    <span class="text-muted small">Belum diatur</span>
                                @endif
                            </td>
                            <td class="text-center">
                               <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.user.edit', $u->id_user) }}" class="btn btn-sm btn-outline-warning" title="Edit User">
                                        <i class="fa-solid fa-user-pen"></i>
                                    </a>
                                    <a href="{{ route('admin.user.access.edit', $u->id_user) }}" class="btn btn-sm btn-outline-info" title="Edit Akses">
                                        <i class="fa-solid fa-user-shield"></i>
                                    </a>

                                    
                                    <form action="{{ route('admin.user.destroy', $u->id_user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini dari sistem?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="fa-solid fa-user-slash d-block fs-2 mb-2 text-secondary"></i> Belum ada akun user terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection