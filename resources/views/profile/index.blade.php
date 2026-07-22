@extends('layouts.app')

@section('title', 'Profil User | B-SMART')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 p-3 rounded-4 d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-check fs-4 me-3 text-success"></i>
            <div class="fw-semibold text-dark">{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4 p-3 rounded-4">
            <div class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Periksa input Anda</div>
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card card-bsmart">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-signature me-2 text-primary"></i>Tanda Tangan Digital</h5>
                    <p class="text-muted small mb-4">Upload gambar tanda tangan untuk kebutuhan pencetakan PDF resmi.</p>

                    <form action="{{ route('profile.signature') }}" method="POST" enctype="multipart/form-data" data-loading>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">File Tanda Tangan</label>
                            <input type="file" name="signature" class="form-control" accept="image/png,image/jpeg" required>
                            <div class="text-muted small mt-2">Maks 2MB. Format: PNG/JPG/JPEG.</div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 rounded-pill fw-semibold">
                            <i class="fa-solid fa-upload me-2"></i> Upload Tanda Tangan
                        </button>
                    </form>

                    @php
                        $u = auth()->user();
                    @endphp
                    @if(!empty($u) && !empty($u->signature_path))
                        <hr class="my-4">
                        <div class="text-muted small mb-2">Saat ini tersimpan:</div>
                        <div class="text-dark fw-semibold" style="word-break:break-word;">{{ basename($u->signature_path) }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-bsmart">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-key me-2 text-primary"></i>Ganti Password</h5>
                    <p class="text-muted small mb-4">Pastikan password baru tidak mudah ditebak.</p>

                    <form action="{{ route('profile.password') }}" method="POST" data-loading>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">Password Saat Ini</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required minlength="8">
                        </div>

                        <button type="submit" class="btn btn-success px-4 rounded-pill fw-semibold">
                            <i class="fa-solid fa-rotate me-2"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

