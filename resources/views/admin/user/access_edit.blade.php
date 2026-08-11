@extends('layouts.app')

@section('title', 'Edit Akses User')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card card-bsmart">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold text-primary m-0"><i class="fa-solid fa-user-shield me-2"></i>Edit Akses User</h5>
                    <div class="text-muted" style="font-size:13px;">{{ $user->nama }} ({{ $user->username }})</div>
                </div>

                <div class="card-body p-4">
                    @php
                        $roles = [
                            'ADMIN','MAKER','CHECKER','KASIR_PUSAT','DIREKTUR','MANAGER_KEUANGAN',
                            'AREA_MANAGER','FINANCE_PROJECT','PROJECT_MANAGER',
                            'KOORDINATOR_KEUANGAN','KOORDINATOR_PK','KOORDINATOR_TC',
                            'KOORDINATOR_DIKLAT','KOORDINATOR_KLINIK','KOORDINATOR_BATRA',
                            'MANAGER_PKP'
                        ];
                    @endphp

                    <form action="{{ route('admin.user.access.update', $user->id_user) }}" method="POST" data-loading>
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px; border-radius:8px;">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                <b>Gagal menyimpan:</b>
                                <ul class="mb-0 mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success py-2 px-3 mb-3" style="font-size:13px; border-radius:8px;">
                                <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
                            </div>
                        @endif

                        <div class="mb-3 text-muted" style="font-size:13px;">
                            <strong>Catatan:</strong>
                            <ul class="mb-0 ps-3" style="font-size:13px;">
                                <li>Klik <strong>Tambah Role</strong> untuk menambah baris role baru.</li>
                                <li>Klik ikon <i class="fa-solid fa-trash text-danger"></i> untuk menghapus baris.</li>
                                <li>Role <code>MAKER</code> / <code>AREA_MANAGER</code> — area-scoped.</li>
                                <li>Role <code>FINANCE_PROJECT</code> / <code>PROJECT_MANAGER</code> / <code>MANAGER_KEUANGAN</code> — project-scoped.</li>
                            </ul>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle" id="aksesTable" style="border: 1px solid rgba(0,0,0,.06); border-radius: 10px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:5%" class="text-center">Aksi</th>
                                        <th>Role</th>
                                        <th>Jabatan (label)</th>
                                        <th>Area</th>
                                        <th style="width:25%">Kode Project (opsional)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($akses as $i => $a)
                                        <tr>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusRole(this)">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <select name="akses[{{ $i }}][role]" class="form-select form-select-sm @error("akses.$i.role") is-invalid @enderror" required>
                                                    @foreach($roles as $r)
                                                        <option value="{{ $r }}" {{ old("akses.$i.role", $a->role ?? '') === $r ? 'selected' : '' }}>{{ $r }}</option>
                                                    @endforeach
                                                </select>
                                                @error("akses.$i.role")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text" name="akses[{{ $i }}][jabatan]" class="form-control form-control-sm @error("akses.$i.jabatan") is-invalid @enderror" required value="{{ old("akses.$i.jabatan", $a->jabatan ?? '') }}" placeholder="Label jabatan">
                                                @error("akses.$i.jabatan")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <select name="akses[{{ $i }}][kode_area]" class="form-select form-select-sm @error("akses.$i.kode_area") is-invalid @enderror" required>
                                                    @foreach($areas as $ar)
                                                        <option value="{{ $ar->kode_area }}" {{ old("akses.$i.kode_area", $a->kode_area ?? '') === $ar->kode_area ? 'selected' : '' }}>
                                                            {{ $ar->nama_area }} ({{ $ar->kode_area }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("akses.$i.kode_area")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                @php $selectedCsv = old("akses.$i.kode_project", $a->kode_project); @endphp
                                                @include('admin.user._project_multiselect', [
                                                    'name' => "akses[$i][kode_project]",
                                                    'projects' => $projects,
                                                    'selectedCsv' => $selectedCsv
                                                ])
                                            </td>
                                        </tr>
                                    @empty
                                        <x-empty-state colspan="5" icon="fa-solid fa-user-slash" title="User belum punya akses." message="Klik &lt;strong&gt;Tambah Role&lt;/strong&gt; di bawah." />
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex gap-2 mb-4">
                            <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="tambahRole()">
                                <i class="fa-solid fa-plus me-1"></i> Tambah Role
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="/admin/user" class="btn btn-light px-4 fw-semibold text-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">Simpan Akses</button>
                        </div>
                    </form>

                    <div class="mt-3">
                        <form action="{{ route('admin.user.access.clear', $user->id_user) }}" method="POST" onsubmit="return confirm('Hapus semua akses user ini?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger fw-semibold">Hapus Semua Akses</button>
                        </form>
                    </div>

                    <template id="role-row-template">
                        <tr>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusRole(this)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                            <td>
                                <select name="akses[IDX][role]" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r }}">{{ $r }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="akses[IDX][jabatan]" class="form-control form-control-sm" required placeholder="Label jabatan">
                            </td>
                            <td>
                                <select name="akses[IDX][kode_area]" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Area --</option>
                                    @foreach($areas as $ar)
                                        <option value="{{ $ar->kode_area }}">{{ $ar->nama_area }} ({{ $ar->kode_area }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                @include('admin.user._project_multiselect', [
                                    'name' => "akses[IDX][kode_project]",
                                    'projects' => $projects,
                                    'selectedCsv' => ''
                                ])
                            </td>
                        </tr>
                    </template>

                    <script>
                        let roleIdx = {{ max($akses->count(), 0) }};

                        function tambahRole() {
                            const tbody = document.querySelector('#aksesTable tbody');
                            const template = document.getElementById('role-row-template');
                            const html = template.innerHTML.replace(/IDX/g, roleIdx++);
                            tbody.insertAdjacentHTML('beforeend', html);
                        }

                        function hapusRole(btn) {
                            const tbody = document.querySelector('#aksesTable tbody');
                            if (tbody.children.length <= 1) {
                                alert('Minimal harus ada 1 role.');
                                return;
                            }
                            btn.closest('tr').remove();
                        }
                    </script>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
