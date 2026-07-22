@extends('layouts.app')

@section('title', 'Tambah Master Budget')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-bsmart">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold text-primary m-0"><i class="fa-solid fa-plus-circle me-2"></i>Tambah Master Budget Baru</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.budget.store') }}" method="POST" data-loading>
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Pilih Hubungan Project</label>
                            <select name="kode_project" class="form-select @error('kode_project') is-invalid @enderror" required>
                                <option value="">-- Pilih Project --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->kode_project }}">{{ $p->kode_project }} - {{ $p->nama_project }}</option>
                                @endforeach
                            </select>
                            @error('kode_project') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Kode Budget (Budgetline)</label>
                            <input type="text" name="kode_budget" class="form-control @error('kode_budget') is-invalid @enderror" placeholder="Contoh: 1.1.1.1 atau BG-38-01" required value="{{ old('kode_budget') }}">
                            @error('kode_budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Nama Budget / Nama Aktivitas</label>
                            <input type="text" name="nama_budget" class="form-control @error('nama_budget') is-invalid @enderror" placeholder="Contoh: Workshop Introduction Posyandu Service" required value="{{ old('nama_budget') }}">
                            @error('nama_budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">Alokasi Dana (Budget)</label>
                            <input type="number" min="0" name="alokasi_dana" class="form-control @error('alokasi_dana') is-invalid @enderror" placeholder="Jumlah alokasi dalam Rupiah" required value="{{ old('alokasi_dana') }}">
                            @error('alokasi_dana') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="text-end pt-2">
                            <a href="{{ route('admin.budget.index') }}" class="btn btn-light px-4 me-2 fw-semibold text-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection