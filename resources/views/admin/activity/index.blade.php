@extends('layouts.app')
@section('title', 'Monitor Aktivitas | B-SMART')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-activity text-danger me-2"></i>Monitor Aktivitas User</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-bsmart">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total Aktivitas Hari Ini</div>
                    <div class="fw-bold fs-4 text-dark">{{ $todayStats->total ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bsmart">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">User Aktif Hari Ini</div>
                    <div class="fw-bold fs-4 text-primary">{{ $todayStats->unique_users ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bsmart">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total User Terdaftar</div>
                    <div class="fw-bold fs-4 text-success">{{ $activeUsers->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-bsmart mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Username</label>
                    <input type="text" name="username" class="form-control form-control-sm" value="{{ request('username') }}" placeholder="Cari username...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Aktivitas</label>
                    <select name="aktivitas" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="login" {{ request('aktivitas') === 'login' ? 'selected' : '' }}>Login</option>
                        <option value="logout" {{ request('aktivitas') === 'logout' ? 'selected' : '' }}>Logout</option>
                        <option value="create_spp" {{ request('aktivitas') === 'create_spp' ? 'selected' : '' }}>Create SPP</option>
                        <option value="approve_spp" {{ request('aktivitas') === 'approve_spp' ? 'selected' : '' }}>Approve SPP</option>
                        <option value="disburse" {{ request('aktivitas') === 'disburse' ? 'selected' : '' }}>Pencairan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                    <a href="{{ route('admin.activity.index') }}" class="btn btn-outline-secondary btn-sm w-100 mt-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-bsmart">
        <div class="card-body p-0">
            <div class="table-responsive">
                @php
                    $sort = request('sort', 'created_at');
                    $dir = request('direction', 'desc');
                @endphp
                <table class="table align-middle text-12">
                    <thead class="bg-table-header">
                        <tr>
                            <th class="border-0 py-3 px-4">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sort === 'created_at' && $dir === 'asc') ? 'desc' : 'asc']) }}" class="sortable {{ $sort === 'created_at' ? 'sort-active' : '' }}">
                                    Waktu
                                    @if($sort === 'created_at')<span class="sort-indicator">{!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}</span>@endif
                                </a>
                            </th>
                            <th class="border-0 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'username', 'direction' => ($sort === 'username' && $dir === 'asc') ? 'desc' : 'asc']) }}" class="sortable {{ $sort === 'username' ? 'sort-active' : '' }}">
                                    Username
                                    @if($sort === 'username')<span class="sort-indicator">{!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}</span>@endif
                                </a>
                            </th>
                            <th class="border-0 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'role', 'direction' => ($sort === 'role' && $dir === 'asc') ? 'desc' : 'asc']) }}" class="sortable {{ $sort === 'role' ? 'sort-active' : '' }}">
                                    Role
                                    @if($sort === 'role')<span class="sort-indicator">{!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}</span>@endif
                                </a>
                            </th>
                            <th class="border-0 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'aktivitas', 'direction' => ($sort === 'aktivitas' && $dir === 'asc') ? 'desc' : 'asc']) }}" class="sortable {{ $sort === 'aktivitas' ? 'sort-active' : '' }}">
                                    Aktivitas
                                    @if($sort === 'aktivitas')<span class="sort-indicator">{!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}</span>@endif
                                </a>
                            </th>
                            <th class="border-0 py-3">Deskripsi</th>
                            <th class="text-center border-0 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'ip_address', 'direction' => ($sort === 'ip_address' && $dir === 'asc') ? 'desc' : 'asc']) }}" class="sortable {{ $sort === 'ip_address' ? 'sort-active' : '' }}">
                                    IP
                                    @if($sort === 'ip_address')<span class="sort-indicator">{!! $dir === 'asc' ? '&#9650;' : '&#9660;' !!}</span>@endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $a)
                        <tr>
                            <td class="px-4 text-secondary">{{ $a->created_at->format('d M Y H:i:s') }}</td>
                            <td class="fw-bold">{{ $a->username }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $a->role }}</span></td>
                            <td>
                                <span class="badge bg-{{ $a->aktivitas === 'login' ? 'success' : ($a->aktivitas === 'logout' ? 'secondary' : 'primary') }}-subtle text-{{ $a->aktivitas === 'login' ? 'success' : ($a->aktivitas === 'logout' ? 'secondary' : 'primary') }}">
                                    {{ $a->aktivitas }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $a->deskripsi }}</td>
                            <td class="text-center"><code class="small text-secondary">{{ $a->ip_address }}</code></td>
                        </tr>
                        @empty
                        <x-empty-state colspan="6" icon="fa-solid fa-clock" title="Belum ada aktivitas tercatat." />
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center p-4">{{ $activities->links() }}</div>
        </div>
    </div>
</div>
@endsection
