@extends('layouts.app')

@section('title', 'Master Budget | CDB Finance')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary m-0"><i class="fa-solid fa-folder-tree me-2"></i>Master Data Kode Budget</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.budget.import.form') }}" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                <i class="fa-solid fa-file-import me-1"></i> Import CSV
            </a>
            <a href="{{ route('admin.budget.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="fa-solid fa-plus me-1"></i> Tambah Budget Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search / Filter -->
    <div class="card card-bsmart mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Cari Budget</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Kode budget, nama, project...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Cari</button>
                </div>
                @if(request('search'))
                <div class="col-auto">
                    <a href="{{ route('admin.budget.index') }}" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-xmark me-1"></i>Reset</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle m-0 table-bsmart">
                    <thead>
                        <tr>
                            <th width="4%" class="text-center border-0 py-3">No</th>
                            <th width="8%" class="text-center border-0 py-3">Project</th>
                            <th width="10%" class="text-center border-0 py-3">Kode Budget</th>
                            <th width="33%" class="border-0 py-3">Nama Komponen Anggaran</th>
                            <th width="13%" class="text-end border-0 py-3">Alokasi Pagu (Budget)</th>
                            <th width="12%" class="text-end border-0 py-3">Terserap (Actual)</th>
                            <th width="12%" class="text-end border-0 py-3">Sisa Saldo</th>
                            <th width="8%" class="text-center border-0 py-3">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($budgets as $index => $b)
                        @php
                            $alokasi = floatval($b->alokasi_dana ?? 0);
                            $terserap = floatval($b->terserap ?? 0);
                            $sisa_saldo = $alokasi - $terserap;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f3f4;" class="hover-actions">
                            <td class="text-center fw-semibold text-secondary py-3">{{ $budgets->firstItem() + $index }}</td>
                            
                            <td class="text-center">
                                <span class="badge bg-light text-dark border badge-custom">
                                    {{ $b->kode_project }}
                                </span>
                            </td>
                            
                            <td class="text-center fw-bold text-primary">
                                <code>{{ $b->kode_budget }}</code>
                            </td>
                            
                            <td class="py-3 pr-3">
                                <div class="fw-semibold text-dark mb-0.5" style="line-height: 1.4;">
                                    {{ $b->nama_budget }}
                                </div>
                            </td>
                            
                            <td class="text-end fw-semibold text-dark">
                                Rp {{ number_format($alokasi, 0, ',', '.') }}
                            </td>
                            
                            <td class="text-end fw-semibold text-secondary">
                                Rp {{ number_format($terserap, 0, ',', '.') }}
                            </td>
                            
                            <td class="text-end fw-bold {{ $sisa_saldo <= 0 ? 'text-danger' : ($sisa_saldo < 2000000 ? 'text-warning' : 'text-success') }}">
                                Rp {{ number_format($sisa_saldo, 0, ',', '.') }}
                            </td>
                            
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.budget.edit', $b->id_budget) }}" class="btn btn-sm btn-outline-warning btn-icon-circle" title="Edit Budget">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.budget.destroy', $b->id_budget) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus budget {{ $b->kode_budget }} ?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-circle" title="Hapus Budget">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <x-empty-state colspan="8" title="Belum Ada Data Budget" message="Belum ada data anggaran yang tersedia." />
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $budgets->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection