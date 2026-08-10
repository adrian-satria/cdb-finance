@extends('layouts.app')

@section('title', isset($area) ? 'Edit Area' : 'Tambah Area')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold text-primary mb-4">
        <i class="fa-solid fa-location-dot me-2"></i>{{ isset($area) ? 'Edit Area' : 'Tambah Area Baru' }}
    </h4>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <form method="POST" action="{{ isset($area) ? route('admin.area.update', $area->kode_area) : route('admin.area.store') }}">
                @csrf
                @if(isset($area)) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label">Kode Area <span class="text-danger">*</span></label>
                    <input type="text" name="kode_area" class="form-control @error('kode_area') is-invalid @enderror"
                        value="{{ old('kode_area', $area->kode_area ?? '') }}"
                        {{ isset($area) ? 'readonly' : '' }} maxlength="20">
                    @error('kode_area') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Area <span class="text-danger">*</span></label>
                    <input type="text" name="nama_area" class="form-control @error('nama_area') is-invalid @enderror"
                        value="{{ old('nama_area', $area->nama_area ?? '') }}" maxlength="100">
                    @error('nama_area') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-regular fa-floppy-disk me-1"></i> Simpan
                    </button>
                    <a href="{{ route('admin.area.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
