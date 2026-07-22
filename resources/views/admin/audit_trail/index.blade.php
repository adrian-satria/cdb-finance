@extends('layouts.app')

@section('title', 'Audit Trail System Log | B-SMART')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-user-shield text-danger me-2"></i>Sistem Audit Trail</h4>
        <p class="text-muted small m-0 mt-1">Rekam jejak aktivitas digital user, manipulasi data transaksi keuangan, dan log otorisasi sistem B-SMART.</p>
    </div>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                @php
                    $sort = request('sort', 'created_at');
                    $dir = request('direction', 'desc');
                @endphp
                <table class="table align-middle border-light-table">
                    <thead class="bg-table-header text-13">
                        <tr>
                            <th width="5%" class="text-center border-0 py-3">No</th>
                            <th width="15%" class="border-0 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sort === 'created_at' && $dir === 'asc') ? 'desc' : 'asc']) }}" class="sortable {{ $sort === 'created_at' ? 'sort-active' : '' }}">
                                    Waktu Kejadian
                                    @if($sort === 'created_at')<span class="sort-indicator">{!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}</span>@endif
                                </a>
                            </th>
                            <th width="12%" class="border-0 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'username', 'direction' => ($sort === 'username' && $dir === 'asc') ? 'desc' : 'asc']) }}" class="sortable {{ $sort === 'username' ? 'sort-active' : '' }}">
                                    Username
                                    @if($sort === 'username')<span class="sort-indicator">{!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}</span>@endif
                                </a>
                            </th>
                            <th width="10%" class="border-0 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'role', 'direction' => ($sort === 'role' && $dir === 'asc') ? 'desc' : 'asc']) }}" class="sortable {{ $sort === 'role' ? 'sort-active' : '' }}">
                                    Role Akses
                                    @if($sort === 'role')<span class="sort-indicator">{!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}</span>@endif
                                </a>
                            </th>
                            <th width="15%" class="border-0 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'aksi', 'direction' => ($sort === 'aksi' && $dir === 'asc') ? 'desc' : 'asc']) }}" class="sortable {{ $sort === 'aksi' ? 'sort-active' : '' }}">
                                    Kategori Aksi
                                    @if($sort === 'aksi')<span class="sort-indicator">{!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}</span>@endif
                                </a>
                            </th>
                            <th width="33%" class="border-0 py-3">Deskripsi Kronologi</th>
                            <th width="10%" class="text-center border-0 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'ip_address', 'direction' => ($sort === 'ip_address' && $dir === 'asc') ? 'desc' : 'asc']) }}" class="sortable {{ $sort === 'ip_address' ? 'sort-active' : '' }}">
                                    IP Address
                                    @if($sort === 'ip_address')<span class="sort-indicator">{!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}</span>@endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-13">
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
                        <x-empty-state colspan="7" icon="fa-solid fa-clock-rotate-left" title="Belum ada rekaman log aktivitas sistem." />
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