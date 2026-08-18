@extends('layouts.app')
@section('title', 'Reimburse | CDB Finance')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center gap-3">
                <i class="fa-solid fa-money-bill-trend-up fs-4 text-primary"></i>
                <h5 class="fw-bold text-dark m-0" style="font-size:20px;">Reimburse LPJ</h5>
            </div>
            <div class="card-body p-4">
                @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                <div class="table-responsive">
                    <table class="table align-middle table-bsmart">
                        <thead><tr><th>No. Reimburse</th><th>LPJ</th><th>UM</th><th class="text-end">Nominal</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($list as $r)
                                <tr>
                                    <td class="fw-semibold">{{ $r->no_reimburse }}</td>
                                    <td>{{ $r->no_lpj }}</td>
                                    <td>{{ $r->pengajuan->no_aju ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($r->total_nominal, 0, ',', '.') }}</td>
                                    <td>
                                        @php $badge=match($r->status_reimburse){'Pending'=>'bg-warning text-dark','Approved'=>'bg-success','Revisi'=>'bg-secondary','Rejected'=>'bg-danger',default=>'bg-light text-dark'}; @endphp
                                        <span class="badge {{ $badge }}">{{ $r->status_reimburse }}</span>
                                    </td>
                                    <td class="text-center"><a href="/uang-muka/reimburse/{{ $r->no_reimburse }}" class="btn btn-sm btn-outline-primary rounded-circle"><i class="fa-solid fa-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada reimburse.</td></tr>
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
