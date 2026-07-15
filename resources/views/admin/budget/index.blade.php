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

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive bg-white rounded-4 p-2 shadow-sm border-0">
                <table class="table align-middle m-0" style="border-color: #f1f3f4;">
                    <thead style="background: #f8fafd; color: #5f6368; font-size: 13px; font-weight: 600;">
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

                    <tbody style="font-size: 13px; color: #3c4043;">
                        @foreach($budgets as $index => $b)
                        @php
                            $alokasi = floatval($b->alokasi_dana ?? 0);
                            $terserap = floatval($b->terserap ?? 0);
                            $sisa_saldo = $alokasi - $terserap;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f3f4;" class="hover-actions">
                            <td class="text-center fw-semibold text-secondary py-3">{{ $budgets->firstItem() + $index }}</td>
                            
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="border-radius: 8px;">
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
                                    <a href="{{ route('admin.budget.edit', $b->id_budget) }}" class="btn btn-sm btn-link text-warning p-1" title="Edit Budget" style="font-size: 15px;">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.budget.destroy', $b->id_budget) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus budget {{ $b->kode_budget }} ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-1 border-0 bg-transparent" title="Hapus Budget" style="font-size: 15px;">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            <div class="d-flex justify-content-center mt-4">
                {{ $budgets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection