@extends('layouts.app')

@section('title', 'Audit Trail System Log | B-SMART')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-user-shield text-danger me-2"></i>Sistem Audit Trail</h4>
        <p class="text-muted small m-0 mt-1">Rekam jejak aktivitas digital user, manipulasi data transaksi keuangan, dan log otorisasi sistem B-SMART.</p>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px; background: #ffffff;">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle" style="border-color: #f1f3f4;">
                    <thead style="background: #f8fafd; color: #5f6368; font-size: 13px;">
                        <tr>
                            <th width="5%" class="text-center border-0 py-3">No</th>
                            <th width="15%" class="border-0 py-3">Waktu Kejadian</th>
                            <th width="12%" class="border-0 py-3">Username</th>
                            <th width="10%" class="border-0 py-3">Role Akses</th>
                            <th width="15%" class="border-0 py-3">Kategori Aksi</th>
                            <th width="33%" class="border-0 py-3">Deskripsi Kronologi</th>
                            <th width="10%" class="text-center border-0 py-3">IP Address</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13.5px;">
                        @forelse($logs as $index => $log)
                        <tr style="border-bottom: 1px solid #f1f3f4;">
                            <td class="text-center fw-semibold text-secondary py-3">{{ $logs->firstItem() + $index }}</td>
                            <td class="text-secondary">{{ date('d M Y | H:i:s', strtotime($log->created_at)) }} WIB</td>
                            <td><span class="fw-bold text-dark">{{ $log->username }}</span></td>
                            <td>
                                @if($log->role == 'ADMIN')
                                    <span class="badge bg-danger-subtle text-danger rounded px-2 py-1" style="font-size: 10px;">ADMIN</span>
                                @elseif($log->role == 'CHECKER')
                                    <span class="badge bg-success-subtle text-success rounded px-2 py-1" style="font-size: 10px;">CHECKER</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary rounded px-2 py-1" style="font-size: 10px;">MAKER</span>
                                @endif
                            </td>
                            <td>
                                @if(str_contains($log->aksi, 'INSERT') || str_contains($log->aksi, 'TAMBAH'))
                                    <span class="text-primary fw-bold"><i class="fa-solid fa-square-plus me-1"></i> {{ $log->aksi }}</span>
                                @elseif(str_contains($log->aksi, 'APPROVAL'))
                                    <span class="text-success fw-bold"><i class="fa-solid fa-shield-check me-1"></i> {{ $log->aksi }}</span>
                                @else
                                    <span class="text-secondary fw-bold"><i class="fa-solid fa-circle-dot me-1"></i> {{ $log->aksi }}</span>
                                @endif
                            </td>
                            <td class="text-dark fw-normal">{{ $log->deskripsi }}</td>
                            <td class="text-center text-muted"><code class="small text-secondary">{{ $log->ip_address }}</code></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5" style="background: #ffffff;">
                                <i class="fa-solid fa-clock-rotate-left d-block fs-2 mb-2 text-black-50"></i> Belum ada rekaman log aktivitas sistem.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            <div class="d-flex justify-content-center mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection