@extends('layouts.app')
@section('title', 'Detail LPJ | CDB Finance')
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-clipboard-check fs-4 text-primary"></i>
                    <h5 class="fw-bold text-dark m-0" style="font-size:20px;">LPJ {{ $lpj->no_lpj }}</h5>
                </div>
                <a href="/uang-muka/lpj/cetak?no_lpj={{ $lpj->no_lpj }}" class="btn-bsmart-secondary" target="_blank">
                    <i class="fa-solid fa-print me-1"></i> Cetak PDF
                </a>
            </div>
            <div class="card-body p-4">
                @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                <div class="row g-3 mb-3">
                    <div class="col-md-3"><label class="form-label-custom">Status</label><br>
                        @php $badge=match($lpj->status_lpj){'Pending'=>'bg-warning text-dark','Approved'=>'bg-success','Revisi'=>'bg-secondary','Rejected'=>'bg-danger',default=>'bg-light text-dark'}; @endphp
                        <span class="badge {{ $badge }} fs-6">{{ $lpj->status_lpj }}</span>
                    </div>
                    <div class="col-md-3"><label class="form-label-custom">UM Induk</label><p class="mb-0 fw-semibold">{{ $lpj->pengajuan->no_aju ?? '-' }}</p></div>
                    <div class="col-md-3"><label class="form-label-custom">Realisasi</label><p class="mb-0 fw-semibold">Rp {{ number_format($lpj->total_realisasi, 0, ',', '.') }}</p></div>
                    <div class="col-md-3"><label class="form-label-custom">Selisih</label><p class="mb-0 fw-semibold">
                        @if($lpj->selisih>0)<span class="text-danger">+{{ number_format($lpj->selisih,0,',','.') }} (refund)</span>
                        @elseif($lpj->selisih<0)<span class="text-success">{{ number_format($lpj->selisih,0,',','.') }} (reimburse)</span>
                        @else Rp 0 @endif</p></div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table align-middle table-bsmart">
                        <thead><tr><th>Uraian</th><th>Kode Budget</th><th class="text-end">Realisasi</th></tr></thead>
                        <tbody>@foreach($lpj->details as $d)<tr><td>{{ $d->keterangan }}</td><td>{{ $d->kode_budget }}</td><td class="text-end">Rp {{ number_format($d->nominal,0,',','.') }}</td></tr>@endforeach</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        @if($canAct)
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4"><h6 class="fw-bold m-0">Tindakan</h6></div>
            <div class="card-body p-4">
                <form action="/uang-muka/lpj/validasi" method="POST">
                    @csrf
                    <input type="hidden" name="no_lpj" value="{{ $lpj->no_lpj }}">
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
