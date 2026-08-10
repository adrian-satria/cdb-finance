@extends('layouts.app')

@section('title', isset($project) ? 'Edit Project' : 'Tambah Project')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold text-primary mb-4">
        <i class="fa-solid fa-diagram-project me-2"></i>{{ isset($project) ? 'Edit Project' : 'Tambah Project Baru' }}
    </h4>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <form method="POST" action="{{ isset($project) ? route('admin.project.update', $project->kode_project) : route('admin.project.store') }}">
                @csrf
                @if(isset($project)) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label">Kode Project <span class="text-danger">*</span></label>
                    <input type="text" name="kode_project" class="form-control @error('kode_project') is-invalid @enderror"
                        value="{{ old('kode_project', $project->kode_project ?? '') }}"
                        {{ isset($project) ? 'readonly' : '' }} maxlength="10">
                    @error('kode_project') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Project <span class="text-danger">*</span></label>
                    <input type="text" name="nama_project" class="form-control @error('nama_project') is-invalid @enderror"
                        value="{{ old('nama_project', $project->nama_project ?? '') }}" maxlength="100">
                    @error('nama_project') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Area Kerja</label>
                    <small class="text-muted d-block mb-2">Pilih area yang terkait dengan project ini.</small>
                    <div class="row">
                        @foreach($areas as $a)
                        <div class="col-md-4 col-lg-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="areas[]"
                                    value="{{ $a->kode_area }}" id="area_{{ $a->kode_area }}"
                                    {{ in_array($a->kode_area, old('areas', isset($project) ? $project->areas->pluck('kode_area')->toArray() : [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="area_{{ $a->kode_area }}">
                                    <strong>{{ $a->kode_area }}</strong> - {{ $a->nama_area }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @error('areas') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-regular fa-floppy-disk me-1"></i> Simpan
                    </button>
                    <a href="{{ route('admin.project.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
