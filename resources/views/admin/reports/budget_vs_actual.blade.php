@extends('layouts.app')
@section('title', 'Budget vs Actual | B-SMART')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-chart-bar text-primary me-2"></i>Budget vs Actual</h4>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Project</label>
                    <select name="kode_project" class="form-select form-select-sm">
                        <option value="">Semua Project</option>
                        @foreach($projects as $p)
                        <option value="{{ $p->kode_project }}" {{ $kodeProject == $p->kode_project ? 'selected' : '' }}>{{ $p->kode_project }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
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

    @if($summary)
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total Alokasi</div>
                    <div class="fw-bold fs-5 text-dark">Rp {{ number_format($summary->total_alokasi ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total Terserap</div>
                    <div class="fw-bold fs-5 text-primary">Rp {{ number_format($summary->total_terserap ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total Sisa</div>
                    <div class="fw-bold fs-5 text-success">Rp {{ number_format($summary->total_sisa ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total Budget Items</div>
                    <div class="fw-bold fs-5 text-info">{{ $summary->total_budget ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle" style="font-size: 12.5px;">
                    <thead style="background: #f8fafd;">
                        <tr>
                            <th class="border-0 py-3">No</th>
                            <th class="border-0 py-3">Project</th>
                            <th class="border-0 py-3">Kode Budget</th>
                            <th class="border-0 py-3">Nama Budget</th>
                            <th class="text-end border-0 py-3">Alokasi</th>
                            <th class="text-end border-0 py-3">Terserap</th>
                            <th class="text-end border-0 py-3">Sisa</th>
                            <th class="text-center border-0 py-3">% Serap</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budgets as $i => $b)
                        <tr>
                            <td class="text-secondary">{{ $budgets->firstItem() + $i }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $b->kode_project }}</span></td>
                            <td><code>{{ $b->kode_budget }}</code></td>
                            <td>{{ $b->nama_budget }}</td>
                            <td class="text-end">Rp {{ number_format($b->alokasi_dana ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($b->terserap ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold {{ ($b->sisa_saldo ?? 0) <= 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($b->sisa_saldo ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar {{ ($b->persentase_serap ?? 0) > 90 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ min($b->persentase_serap ?? 0, 100) }}%"></div>
                                </div>
                                <small class="text-muted">{{ $b->persentase_serap ?? 0 }}%</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">{{ $budgets->links() }}</div>
        </div>
    </div>
</div>
@endsection
