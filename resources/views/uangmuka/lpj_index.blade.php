@extends('layouts.app')
@section('title', 'LPJ Uang Muka | Finance Management Demo')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-clipboard-check fs-4 text-primary"></i>
                    <h5 class="fw-bold text-dark m-0" style="font-size:20px;">LPJ Uang Muka</h5>
                </div>
                <a href="/uang-muka?register=outstanding" class="btn-bsmart-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Register UM</a>
            </div>
            <div class="card-body p-4">
                @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                <div class="table-responsive">
                    <table class="table align-middle table-bsmart">
                        <thead><tr><th>No. LPJ</th><th>UM</th><th class="text-end">Realisasi</th><th class="text-end">Selisih</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($list as $lpj)
                                <tr>
                                    <td class="fw-semibold">{{ $lpj->no_lpj }}</td>
                                    <td>{{ $lpj->pengajuan->no_aju ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($lpj->total_realisasi, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        @if($lpj->selisih > 0)<span class="text-danger">+{{ number_format($lpj->selisih, 0, ',', '.') }} (refund)</span>
                                        @elseif($lpj->selisih < 0)<span class="text-success">{{ number_format($lpj->selisih, 0, ',', '.') }} (reimburse)</span>
                                        @else Rp 0 @endif
                                    </td>
                                    <td>
                                        @php $badge = match($lpj->status_lpj){'Pending'=>'bg-warning text-dark','Approved'=>'bg-success','Revisi'=>'bg-secondary','Rejected'=>'bg-danger',default=>'bg-light text-dark'}; @endphp
                                        <span class="badge {{ $badge }}">{{ $lpj->status_lpj }}</span>
                                    </td>
                                    <td class="text-center"><a href="/uang-muka/lpj/{{ $lpj->no_lpj }}" class="btn btn-sm btn-outline-primary rounded-circle"><i class="fa-solid fa-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada LPJ.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $list->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

