@extends('layouts.app')

@section('title', 'Import Master Budget')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary m-0"><i class="fa-solid fa-file-import me-2"></i>Import Master Budget (CSV)</h4>
        <a href="{{ route('admin.budget.index') }}" class="btn btn-light btn-sm px-3 fw-semibold text-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @if(session('import_success'))
        @php
            $s = session('import_summary', []);
        @endphp
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Import selesai.
            <ul class="mb-0">
                <li>Insert: <b>{{ $s['inserted'] ?? 0 }}</b></li>
                <li>Update: <b>{{ $s['updated'] ?? 0 }}</b></li>
                <li>Skip: <b>{{ $s['skipped'] ?? 0 }}</b></li>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('import_error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="fw-semibold mb-2">Beberapa baris tidak berhasil diproses:</div>
            <ul class="mb-0">
                @foreach((array) session('import_error') as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="mb-4">
                <div class="fw-semibold text-secondary small mb-2">Format file CSV</div>
                <div class="text-muted" style="font-size: 13px; line-height: 1.6;">
                    Header wajib (case-insensitive):
                    <code>kode_project,kode_budget,nama_budget,alokasi_dana</code>
                    <br>
                    <b>nama_budget</b> opsional, tapi jika ada akan ikut update.
                </div>
                <div class="mt-2 text-muted" style="font-size: 13px; line-height: 1.6;">
                    Contoh baris:
                    <code>38,1.1,Component 1 ...,25000000</code>
                </div>
                <div class="mt-3">
                    <a class="btn btn-sm btn-outline-secondary fw-semibold" href="{{ asset('storage/templates/master_budget_import_template.csv') }}" download>
                        <i class="fa-solid fa-download me-2"></i>Download Template CSV
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.budget.import') }}" method="POST" enctype="multipart/form-data" data-loading>
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small">Pilih File CSV</label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,text/csv" required>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.budget.index') }}" class="btn btn-light fw-semibold text-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="fa-solid fa-upload me-2"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

