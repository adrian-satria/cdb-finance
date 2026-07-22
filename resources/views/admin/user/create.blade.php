@extends('layouts.app')

@section('title', 'Tambah Akun Pengguna')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card card-bsmart">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold text-primary m-0"><i class="fa-solid fa-user-plus me-2"></i>Daftarkan Akun Pengguna Baru</h5>
                </div>
                <div class="card-body p-4">
                    <form action="/admin/user/store" method="POST" data-loading>
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Nama Lengkap Anggota</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Nama lengkap sesuai KTP/ID" required value="{{ old('nama') }}">
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Username Log In</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Contoh: adrian_putra" required value="{{ old('username') }}">
                            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Kata Sandi (Password)</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 4 karakter kombinasi" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="text-end pt-2">
                            <a href="/admin/user" class="btn btn-light px-4 me-2 fw-semibold text-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">Daftarkan Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection