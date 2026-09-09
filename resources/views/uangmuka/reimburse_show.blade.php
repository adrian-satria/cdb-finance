@extends('layouts.app')
@section('title', 'Detail Reimburse | Finance Management Demo')
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center gap-3">
                <i class="fa-solid fa-money-bill-trend-up fs-4 text-primary"></i>
                <h5 class="fw-bold text-dark m-0" style="font-size:20px;">Reimburse {{ $reimburse->no_reimburse }}</h5>
            </div>
            <div class="card-body p-4">
                @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><label class="form-label-custom">Status</label><br>
                        @php $badge=match($reimburse->status_reimburse){'Pending'=>'bg-warning text-dark','Approved'=>'bg-success','Revisi'=>'bg-secondary','Rejected'=>'bg-danger',default=>'bg-light text-dark'}; @endphp
                        <span class="badge {{ $badge }} fs-6">{{ $reimburse->status_reimburse }}</span>
                    </div>
                    <div class="col-md-3"><label class="form-label-custom">LPJ</label><p class="mb-0 fw-semibold">{{ $reimburse->no_lpj }}</p></div>
                    <div class="col-md-3"><label class="form-label-custom">UM Induk</label><p class="mb-0 fw-semibold">{{ $reimburse->pengajuan->no_aju ?? '-' }}</p></div>
                    <div class="col-md-3"><label class="form-label-custom">Nominal</label><p class="mb-0 fw-semibold">Rp {{ number_format($reimburse->total_nominal, 0, ',', '.') }}</p></div>
                </div>
                <p class="text-muted">Reimburse otomatis terbentuk dari LPJ karena realisasi melebihi uang muka (selisih negatif).</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        @if($canAct)
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4"><h6 class="fw-bold m-0">Tindakan</h6></div>
            <div class="card-body p-4">
                <form action="/uang-muka/reimburse/validasi" method="POST">
                    @csrf
                    <input type="hidden" name="no_reimburse" value="{{ $reimburse->no_reimburse }}">
                    <div class="mb-2"><select name="aksi" class="form-select-custom" required><option value="approve">Setuju</option><option value="revise">Revisi</option><option value="reject">Tolak</option></select></div>
                    <div class="mb-2"><input type="text" name="alasan" class="form-control-custom" placeholder="Alasan (jika revisi/tolak)"></div>
                    <button type="submit" class="btn-bsmart-primary w-100"><i class="fa-solid fa-check me-1"></i> Proses</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

