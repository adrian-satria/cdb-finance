@extends('layouts.app')
@section('title', 'Performa Area | Finance Management Demo')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-map-location-dot text-info me-2"></i>Performa Area</h4>
    </div>

    <div class="card card-bsmart mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        @for($y = date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted">Project</label>
                    <select name="kode_project" class="form-select form-select-sm">
                        <option value="">Semua Project</option>
                        @foreach($projects as $p)
                        <option value="{{ $p->kode_project }}" {{ ($kodeProject ?? '') == $p->kode_project ? 'selected' : '' }}>{{ $p->kode_project }} - {{ $p->nama_project }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Dari Tgl</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Sampai Tgl</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-success btn-sm w-100" style="white-space: nowrap;">
                        <i class="fa-solid fa-download me-1"></i> CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle text-12">
                    <thead class="bg-table-header">
                        <tr>
                            <th class="border-0 py-3">#</th>
                            <th class="border-0 py-3">Area</th>
                            <th class="text-end border-0 py-3">Total SPP</th>
                            <th class="text-end border-0 py-3">Total Nominal</th>
                            <th class="text-end border-0 py-3">Rata-rata</th>
                            <th class="text-end border-0 py-3">Terealisasi</th>
                            <th class="text-end border-0 py-3">% Realisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($areaData as $i => $a)
                        @php $persen = $a->total_nominal > 0 ? round(($a->realized / $a->total_nominal) * 100, 1) : 0; @endphp
                        <tr>
                            <td class="text-secondary">{{ $i + 1 }}</td>
                            <td class="fw-bold">{{ $a->kode_area }}</td>
                            <td class="text-end">{{ number_format($a->total_spp, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($a->total_nominal, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($a->rata_rata, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($a->realized, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <span class="badge bg-{{ $persen > 75 ? 'success' : ($persen > 50 ? 'warning' : 'danger') }}">{{ $persen }}%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

