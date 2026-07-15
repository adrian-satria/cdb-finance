@extends('layouts.app')

@section('title', 'Ubah Profil Pengguna')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0" style="border-radius: var(--radius-lg);">
                <div class="card-header bg-white py-3 border-light">
                    <h5 class="fw-bold text-primary m-0"><i class="fa-solid fa-user-gear me-2"></i>Ubah Profil Pengguna</h5>
                </div>
                <div class="card-body p-4">
                    <form action="/admin/user/{{ $user->id_user }}/update" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" required value="{{ old('nama', $user->nama) }}">
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Username</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" required value="{{ old('username', $user->username) }}">
                            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Kata Sandi Baru (Kosongkan jika tidak diganti)</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Isi hanya jika ingin mereset password akun">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="text-end pt-2">
                            <a href="/admin/user" class="btn btn-light px-4 me-2 fw-semibold text-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">Perbarui Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection