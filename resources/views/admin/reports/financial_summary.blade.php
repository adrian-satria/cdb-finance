@extends('layouts.app')
@section('title', 'Ringkasan Keuangan | B-SMART')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-file-invoice text-success me-2"></i>Ringkasan Keuangan</h4>
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

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-calendar me-2"></i>Data Bulanan</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle" style="font-size: 12px;">
                            <thead style="background: #f8fafd;">
                                <tr>
                                    <th class="border-0 py-2">Bulan</th>
                                    <th class="text-end border-0 py-2">Total SPP</th>
                                    <th class="text-end border-0 py-2">Total Nominal</th>
                                    <th class="text-center border-0 py-2">Pending</th>
                                    <th class="text-center border-0 py-2">Disetujui</th>
                                    <th class="text-center border-0 py-2">Dicairkan</th>
                                    <th class="text-center border-0 py-2">Ditolak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                @endphp
                                @foreach($monthlyData as $m)
                                <tr>
                                    <td class="fw-bold">{{ $months[(int)$m->bulan] ?? $m->bulan }} {{ $m->tahun }}</td>
                                    <td class="text-end">{{ number_format($m->total_spp, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($m->total_nominal, 0, ',', '.') }}</td>
                                    <td class="text-center"><span class="badge bg-warning">{{ $m->pending }}</span></td>
                                    <td class="text-center"><span class="badge bg-success">{{ $m->approved }}</span></td>
                                    <td class="text-center"><span class="badge bg-info">{{ $m->disbursed }}</span></td>
                                    <td class="text-center"><span class="badge bg-danger">{{ $m->rejected }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-project-diagram me-2"></i>Ringkasan Per Project</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle" style="font-size: 12px;">
                            <thead style="background: #f8fafd;">
                                <tr>
                                    <th class="border-0 py-2">Project</th>
                                    <th class="text-end border-0 py-2">Total SPP</th>
                                    <th class="text-end border-0 py-2">Total Nominal</th>
                                    <th class="text-end border-0 py-2">Terealisasi</th>
                                    <th class="text-end border-0 py-2">% Realisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projectSummary as $p)
                                @php $persen = $p->total_nominal > 0 ? round(($p->total_disbursed / $p->total_nominal) * 100, 1) : 0; @endphp
                                <tr>
                                    <td><span class="badge bg-light text-dark border">{{ $p->kode_project }}</span></td>
                                    <td class="text-end">{{ number_format($p->total_spp, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($p->total_nominal, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($p->total_disbursed, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <div class="progress" style="width: 80px; height: 6px;">
                                                <div class="progress-bar bg-success" style="width: {{ $persen }}%"></div>
                                            </div>
                                            <small>{{ $persen }}%</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
