@extends('layouts.app')
@section('title', 'Performa Area | B-SMART')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-map-location-dot text-info me-2"></i>Performa Area</h4>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        @for($y = date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle" style="font-size: 12.5px;">
                    <thead style="background: #f8fafd;">
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
