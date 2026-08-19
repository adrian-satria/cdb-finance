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
                    <a href="/uang-muka" class="btn-bsmart-secondary">
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
                                <th>No</th>
                                <th>Tgl Pengajuan</th>
                                <th>Nomor Uang Muka</th>
                                <th>Nama</th>
                                <th>Project/Bidang</th>
                                <th>Detil</th>
                                <th class="text-end">Jumlah</th>
                                <th>Tanggal Dicairkan</th>
                                <th>Tanggal Akan Diselesaikan</th>
                                <th>Tanggal Kas Bon Selesai</th>
                                <th>Umur Kas Bon</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($list as $um)
                                @php
                                    $nama = optional($um->pengaju)->nama_lengkap ?? optional($um->pengaju)->name ?? optional($um->pengaju)->username ?? '-';
                                    $no = ($list->currentPage() - 1) * $list->perPage() + $loop->iteration;
                                @endphp
                                <tr>
                                    <td class="text-muted">{{ $no }}</td>
                                    <td>{{ $um->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="fw-semibold">{{ $um->no_aju }}</td>
                                    <td>{{ $nama }}</td>
                                    <td>{{ $um->kode_project }} / {{ $um->kode_area }}</td>
                                    <td>{{ $um->keterangan ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($um->total_nominal, 0, ',', '.') }}</td>
                                    <td>{{ $um->tanggal_cair?->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $um->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $um->refund_tanggal?->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $um->umur_kas_bon !== null ? $um->umur_kas_bon.' hari' : '-' }}</td>
                                    <td class="text-center">
                                        <a href="/uang-muka/{{ $um->no_aju }}" class="btn btn-sm btn-outline-primary rounded-circle" title="Lihat"><i class="fa-solid fa-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center text-muted py-4">Belum ada pengajuan uang muka.</td></tr>
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
