@extends('layouts.app')

@section('title', 'Edit Akses User')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0" style="border-radius: var(--radius-lg);">
                <div class="card-header bg-white py-3 border-light">
                    <h5 class="fw-bold text-primary m-0"><i class="fa-solid fa-user-shield me-2"></i>Edit Akses User</h5>
                    <div class="text-muted" style="font-size:13px;">{{ $user->nama }} ({{ $user->username }})</div>
                </div>

                <div class="card-body p-4">
                    @php
                        // Multi-row form: akses[]
                        $roles = [
                            'ADMIN','MAKER','CHECKER','KASIR_PUSAT','DIREKTUR','MANAGER_KEUANGAN',
                            'AREA_MANAGER','FINANCE_PROJECT','PROJECT_MANAGER',
                            'KOORDINATOR_KEUANGAN','KOORDINATOR_PK','KOORDINATOR_TC',
                            'KOORDINATOR_DIKLAT','KOORDINATOR_KLINIK','KOORDINATOR_BATRA',
                            'MANAGER_PKP'
                        ];
                    @endphp

                    <form action="{{ route('admin.user.access.update', $user->id_user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Tampilkan error summary jika ada --}}
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
                            Data multi-role disimpan sebagai beberapa baris di <b>user_access</b>.
                            <br>
                            <strong>Catatan penting:</strong>
                            <ul class="mb-0 ps-3" style="font-size:13px;">
                                <li><strong>Role area-scoped</strong> seperti <code>MAKER</code> dan <code>AREA_MANAGER</code> akan memfilter berdasarkan <code>kode_area</code>.</li>
                                <li><strong>Role project-scoped</strong> seperti <code>FINANCE_PROJECT</code>, <code>PROJECT_MANAGER</code>, dan <code>MANAGER_KEUANGAN</code> akan memfilter berdasarkan <code>kode_project</code>.</li>
                                <li>Untuk role project-scoped, pastikan proyek dipilih dengan benar. <code>kode_area</code> dapat diisi <strong>PUSAT</strong> sebagai unit login default.</li>
                            </ul>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle" style="border: 1px solid rgba(0,0,0,.06); border-radius: 10px; overflow:hidden;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:8%" class="text-center">#</th>
                                        <th>Role</th>
                                        <th>Jabatan (label)</th>
                                        <th>Area</th>
                                        <th style="width:25%">Kode Project (opsional)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($akses as $i => $a)
                                        @php
                                            $rowIndex = $i;
                                        @endphp
                                        <tr>
                                            <td class="text-center fw-semibold text-secondary">{{ $i+1 }}</td>

                                            <td>
                                                <select name="akses[{{ $rowIndex }}][role]" class="form-select form-select-sm @error("akses.$rowIndex.role") is-invalid @enderror" required>
                                                    @foreach($roles as $r)
                                                        <option value="{{ $r }}" {{ old("akses.$rowIndex.role", $a->role ?? '') === $r ? 'selected' : '' }}>{{ $r }}</option>
                                                    @endforeach
                                                </select>
                                                @error("akses.$rowIndex.role")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror

                                                {{-- delete row button (avoid nested <form> inside main PUT form) --}}
                                                <div class="mt-2">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger w-100"
                                                        onclick="document.getElementById('delete-row-form-{{ $rowIndex }}').submit()"
                                                    >
                                                        Hapus
                                                    </button>

                                                    {{-- actual delete form will be rendered after the main form --}}
                                                </div>
                                            </td>

                                            <td>
                                                <input type="text" name="akses[{{ $rowIndex }}][jabatan]" class="form-control form-control-sm @error("akses.$rowIndex.jabatan") is-invalid @enderror" required value="{{ old("akses.$rowIndex.jabatan", $a->jabatan ?? '') }}" placeholder="Label jabatan">
                                                @error("akses.$rowIndex.jabatan")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            <td>
                                                <select name="akses[{{ $rowIndex }}][kode_area]" class="form-select form-select-sm @error("akses.$rowIndex.kode_area") is-invalid @enderror" required>
                                                    @foreach($areas as $ar)
                                                        <option value="{{ $ar->kode_area }}" {{ old("akses.$rowIndex.kode_area", $a->kode_area ?? '') === $ar->kode_area ? 'selected' : '' }}>
                                                            {{ $ar->nama_area }} ({{ $ar->kode_area }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("akses.$rowIndex.kode_area")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted role-hint" data-row="{{ $rowIndex }}"></div>
                                            </td>

                                        <td>
                                            @php
                                                $selectedCsv = old("akses.$rowIndex.kode_project", $a->kode_project);
                                            @endphp
                                            @include('admin.user._project_multiselect', [
                                                'name' => "akses[$rowIndex][kode_project]",
                                                'projects' => $projects,
                                                'selectedCsv' => $selectedCsv
                                            ])
                                            <div class="form-text text-muted project-hint" data-row="{{ $rowIndex }}"></div>
                                        </td>

                                        </tr>

                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">User belum punya akses.</td>
                                        </tr>
                                    @endforelse

                                    {{-- Add one extra row untuk kasus user punya 0 akses atau tambah role baru --}}
                                    @php $extraIdx = $akses->count(); @endphp
                                    <tr>
                                        <td class="text-center fw-semibold text-secondary">+</td>
                                        <td>
                                            <select name="akses[{{ $extraIdx }}][role]" class="form-select form-select-sm @error('akses.'.$extraIdx.'.role') is-invalid @enderror">
                                                <option value="">-- Pilih Role --</option>
                                                @foreach($roles as $r)
                                                    <option value="{{ $r }}" {{ old("akses.$extraIdx.role") === $r ? 'selected' : '' }}>{{ $r }}</option>
                                                @endforeach
                                            </select>
                                            @error("akses.$extraIdx.role")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text" name="akses[{{ $extraIdx }}][jabatan]" class="form-control form-control-sm @error('akses.'.$extraIdx.'.jabatan') is-invalid @enderror" value="{{ old("akses.$extraIdx.jabatan") }}" placeholder="Label jabatan (opsional)">
                                            @error("akses.$extraIdx.jabatan")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <select name="akses[{{ $extraIdx }}][kode_area]" class="form-select form-select-sm @error('akses.'.$extraIdx.'.kode_area') is-invalid @enderror">
                                                <option value="">-- Pilih Area --</option>
                                                @foreach($areas as $ar)
                                                    <option value="{{ $ar->kode_area }}" {{ old("akses.$extraIdx.kode_area") === $ar->kode_area ? 'selected' : '' }}>{{ $ar->nama_area }} ({{ $ar->kode_area }})</option>
                                                @endforeach
                                            </select>
                                            @error("akses.$extraIdx.kode_area")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text text-muted role-hint" data-row="{{ $extraIdx }}"></div>
                                        </td>
                                        <td>
                                            @include('admin.user._project_multiselect', [
                                                'name' => "akses[$extraIdx][kode_project]",
                                                'projects' => $projects,
                                                'selectedCsv' => old("akses.$extraIdx.kode_project")
                                            ])
                                            <div class="form-text text-muted project-hint" data-row="{{ $extraIdx }}"></div>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="/admin/user" class="btn btn-light px-4 fw-semibold text-secondary">Batal</a>

                            <div class="d-flex gap-2">
                                {{-- Hapus akses (opsional). Jika route DELETE belum aktif, nonaktifkan tombol ini. --}}
                                <form action="{{ route('admin.user.access.clear', $user->id_user) }}" method="POST" onsubmit="return confirm('Hapus semua akses user ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger fw-semibold">Hapus Akses</button>
                                </form>

                                <button type="submit" class="btn btn-primary px-4 fw-semibold">Simpan Akses</button>
                            </div>

                        </div>
                    </form>

                    {{-- Delete row forms OUTSIDE main PUT form --}}
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const projectRoles = ['FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN'];

                            const buildHint = (role) => {
                                if (projectRoles.includes(role)) {
                                    return 'Project-scoped role: pilih kode_project. Untuk role ini, pilih kode_area PUSAT atau area login default.';
                                }
                                return 'Area-scoped role: pilih kode_area. Kode_project hanya diperlukan untuk peran project-scoped.';
                            };

                            const updateRow = (rowIndex) => {
                                const roleSelect = document.querySelector(`select[name='akses[${rowIndex}][role]']`);
                                const projectSelect = document.querySelector(`input[name='akses[${rowIndex}][kode_project]']`);
                                const roleHint = document.querySelector(`.role-hint[data-row='${rowIndex}']`);
                                const projectHint = document.querySelector(`.project-hint[data-row='${rowIndex}']`);

                                if (!roleSelect || !projectSelect || !roleHint || !projectHint) {
                                    return;
                                }

                                const roleValue = roleSelect.value;
                                roleHint.textContent = buildHint(roleValue);

                                if (projectRoles.includes(roleValue)) {
                                    projectSelect.required = true;
                                    projectHint.textContent = 'Wajib: pilih kode_project untuk role project-scoped.';
                                } else {
                                    projectSelect.required = false;
                                    projectHint.textContent = 'Opsional: pilih project jika Anda ingin mengikat role ini ke satu project.';
                                }
                            };

                            document.querySelectorAll('select[name$="[role]"]').forEach((roleSelect) => {
                                const match = roleSelect.name.match(/akses\[(\d+)\]\[role\]$/);
                                if (!match) return;
                                const rowIndex = match[1];
                                roleSelect.addEventListener('change', () => updateRow(rowIndex));
                                updateRow(rowIndex);
                            });
                        });
                    </script>

                    @forelse($akses as $i => $a)
                        <form
                            id="delete-row-form-{{ $i }}"
                            action="{{ route('admin.user.access.delete_row', $user->id_user) }}"
                            method="POST"
                            class="d-none"
                        >
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="role" value="{{ $a->role }}">
                            <input type="hidden" name="kode_area" value="{{ $a->kode_area }}">
                            <input type="hidden" name="kode_project" value="{{ $a->kode_project }}">
                        </form>
                    @empty
                        {{-- no-op --}}
                    @endforelse

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
