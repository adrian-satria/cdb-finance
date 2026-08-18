@extends('layouts.app')

@section('title', 'Uang Muka | CDB Finance - B-SMART')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-money-bill-wave fs-4 text-primary"></i>
                    <h5 class="fw-bold text-dark m-0" style="font-size:20px;">Pengajuan Uang Muka (UM)</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="/uang-muka?register=outstanding" class="btn-bsmart-secondary">
                        <i class="fa-solid fa-clipboard-list me-1"></i> Register UM
                    </a>
                    <a href="/uang-muka/tambah" class="btn-bsmart-primary">
                        <i class="fa-solid fa-plus me-1"></i> Input UM Baru
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-xmark me-1"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="mb-3 d-flex gap-2 flex-wrap">
                    @php $statuses = ['Pending','Approved','Cair','Revisi','Rejected']; @endphp
                    <a href="/uang-muka" class="badge rounded-pill {{ !request('status') && !request('register') ? 'bg-primary' : 'bg-secondary' }} text-decoration-none py-2 px-3">Semua</a>
                    @foreach($statuses as $s)
                        <a href="/uang-muka?status={{ $s }}" class="badge rounded-pill {{ request('status')==$s ? 'bg-primary' : 'bg-light text-dark border' }} text-decoration-none py-2 px-3">{{ $s }}</a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-bsmart">
                        <thead>
                            <tr>
                                <th>No. AJU</th>
                                <th>Tanggal</th>
                                <th>Project</th>
                                <th>Area</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Sisa LPJ</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($list as $um)
                                <tr>
                                    <td class="fw-semibold">{{ $um->no_aju }}</td>
                                    <td>{{ $um->tanggal?->format('d/m/Y') }}</td>
                                    <td>{{ $um->kode_project }}</td>
                                    <td>{{ $um->kode_area }}</td>
                                    <td class="text-end">Rp {{ number_format($um->total_nominal, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        @if($um->sisa_lpj > 0)
                                            <span class="text-danger">Rp {{ number_format($um->sisa_lpj, 0, ',', '.') }}</span>
                                        @else
                                            Rp 0
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badge = match($um->status_um) {
                                                'Pending' => 'bg-warning text-dark',
                                                'Approved' => 'bg-success',
                                                'Cair' => 'bg-primary',
                                                'Revisi' => 'bg-secondary',
                                                'Rejected' => 'bg-danger',
                                                default => 'bg-light text-dark'
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $um->status_um }}</span>
                                        @if($um->isOverdue())
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Jatuh Tempo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="/uang-muka/{{ $um->no_aju }}" class="btn btn-sm btn-outline-primary rounded-circle"><i class="fa-solid fa-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada pengajuan uang muka.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $list->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
