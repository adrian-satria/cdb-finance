@extends('layouts.app')
@push('styles')
@vite(['resources/css/dashboard.css'])
@endpush
@section('title', 'Dashboard | B-SMART')
@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0">
            <i class="fa-solid fa-gauge-high text-primary me-2"></i>Dashboard
            <small class="text-muted fs-6 fw-normal ms-2">{{ session('jabatan') ?? session('role') }}</small>
        </h4>
    </div>

    {{-- Project Filter (Admin only) --}}
    @if(session('role') === 'ADMIN' && $projects->isNotEmpty())
    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-auto">
            <label class="form-label small text-muted mb-1">Filter Project</label>
            <select name="kode_project" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 220px;">
                <option value="">Semua Project</option>
                @foreach($projects as $p)
                    <option value="{{ $p->kode_project }}" {{ $selectedProject == $p->kode_project ? 'selected' : '' }}>
                        {{ $p->kode_project }} - {{ $p->nama_project }}
                    </option>
                @endforeach
            </select>
        </div>
        @if($selectedProject)
        <div class="col-auto">
            <a href="/dashboard" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-xmark me-1"></i>Reset</a>
        </div>
        @endif
    </form>
    @endif

    {{-- Financial Summary Cards --}}
    @if(isset($totalBudget))
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; border-left: 4px solid #2563eb !important;">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Budget</p>
                    <h5 class="fw-bold text-primary m-0">Rp {{ number_format($totalBudget, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <p class="text-muted small mb-1">Terserap</p>
                    <h5 class="fw-bold text-success m-0">Rp {{ number_format($totalTerserap, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <p class="text-muted small mb-1">Sisa Budget</p>
                    <h5 class="fw-bold text-warning m-0">Rp {{ number_format($sisaBudget, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; border-left: 4px solid {{ $persenUtilisasi > 90 ? '#ef4444' : '#06b6d4' }} !important;">
                <div class="card-body">
                    <p class="text-muted small mb-1">Utilisasi</p>
                    <h5 class="fw-bold m-0 {{ $persenUtilisasi > 90 ? 'text-danger' : 'text-info' }}">
                        {{ $persenUtilisasi }}%
                        @if($persenUtilisasi > 90) <i class="fa-solid fa-triangle-exclamation ms-1"></i> @endif
                    </h5>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Critical Budget Alert (Admin only) --}}
    @if(isset($criticalBudgets) && $criticalBudgets->isNotEmpty())
    <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation fs-5"></i>
        <div>
            <strong>Peringatan!</strong> {{ $criticalBudgets->count() }} project dengan utilisasi &ge; 90%:
            @foreach($criticalBudgets as $b)
                <span class="badge bg-danger ms-1">{{ $b->kode_project }} ({{ $b->persen }}%)</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Pending</p>
                            <h3 class="fw-bold text-warning m-0">{{ $countPending ?? 0 }}</h3>
                        </div>
                        <div class="rounded-circle bg-warning-subtle p-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-clock text-warning fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Approved</p>
                            <h3 class="fw-bold text-success m-0">{{ $countApproved ?? 0 }}</h3>
                        </div>
                        <div class="rounded-circle bg-success-subtle p-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-check-circle text-success fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; border-left: 4px solid #ef4444 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Rejected</p>
                            <h3 class="fw-bold text-danger m-0">{{ $countRejected ?? 0 }}</h3>
                        </div>
                        <div class="rounded-circle bg-danger-subtle p-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-ban text-danger fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; border-left: 4px solid #06b6d4 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Total Disbursed</p>
                            <h3 class="fw-bold text-info m-0">{{ $countDisbursed ?? 0 }}</h3>
                        </div>
                        <div class="rounded-circle bg-info-subtle p-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-money-bill-wave text-info fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Budget per Project Progress Bars (Admin only) --}}
    @if(isset($budgetPerProject) && $budgetPerProject->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-simple me-2 text-primary"></i>Budget vs Actual per Project</h6>
            @foreach($budgetPerProject as $b)
                @php
                    $barColor = $b->persen >= 90 ? 'bg-danger' : ($b->persen >= 75 ? 'bg-warning' : 'bg-primary');
                    $textColor = $b->persen >= 90 ? 'text-danger' : 'text-dark';
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold {{ $textColor }}">
                            {{ $b->kode_project }}
                            @if($b->persen >= 90) <i class="fa-solid fa-triangle-exclamation ms-1"></i> @endif
                        </span>
                        <span class="text-muted">
                            Rp {{ number_format($b->total_terserap, 0, ',', '.') }} / Rp {{ number_format($b->total_alokasi, 0, ',', '.') }} ({{ $b->persen }}%)
                        </span>
                    </div>
                    <div class="progress" style="height: 16px; border-radius: 8px;">
                        <div class="progress-bar {{ $barColor }}" 
                             style="width: {{ min($b->persen, 100) }}%; font-size: 10px; font-weight: 600;">
                            {{ $b->persen }}%
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Monthly Chart --}}
    @if(isset($monthlyChart) && $monthlyChart->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-chart-line me-2 text-success"></i>Tren Pengajuan Bulanan ({{ date('Y') }})</h6>
                <small class="text-muted">Total SPP: <span class="fw-bold text-dark">{{ $monthlyChart->sum('total') }}</span></small>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    {{-- My Tasks --}}
    <div class="card border-0 shadow-sm" style="border-radius: 14px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-tasks me-2 text-primary"></i>My Tasks</h6>
                <x-button href="/spp" size="sm" type="outline-primary">Lihat Semua</x-button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle" style="font-size: 12.5px;">
                    <thead style="background: #f8fafd;">
                        <tr>
                            <th class="border-0 py-2">No. Surat</th>
                            <th class="border-0 py-2">Project</th>
                            <th class="border-0 py-2">Area</th>
                            <th class="text-end border-0 py-2">Nominal</th>
                            <th class="text-center border-0 py-2">Status</th>
                            <th class="text-center border-0 py-2">Posisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $s)
                        <tr>
                            <td><a href="javascript:void(0)" class="fw-bold text-dark text-decoration-none" onclick="showDetailSpp('{{ $s->no_surat }}')">{{ $s->no_surat }}</a></td>
                            <td><x-badge type="light">{{ $s->kode_project }}</x-badge></td>
                            <td>{{ $s->kode_area }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($s->total_nominal, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @php
                                    $badgeType = match($s->status_surat) {
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        'Disbursed' => 'primary',
                                        default => 'warning',
                                    };
                                @endphp
                                <x-badge type="{{ $badgeType }}">{{ $s->status_surat }}</x-badge>
                            </td>
                            <td class="text-center"><x-badge type="light">{{ $s->posisi_saat_ini }}</x-badge></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fa-regular fa-circle-check d-block fs-3 mb-2"></i>
                                Tidak ada task yang perlu ditindaklanjuti.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('modals')
@include('spp.modals')
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    var ctx = document.getElementById('monthlyChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [@foreach($monthlyChart as $mc) '{{ $mc['bulan'] }}', @endforeach],
                datasets: [{
                    label: 'Jumlah SPP',
                    data: [@foreach($monthlyChart as $mc) {{ $mc['total'] }}, @endforeach],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                var data = @json($monthlyChart);
                                return 'Nominal: Rp ' + (data[context.dataIndex]?.nominal ?? 0).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: 'rgba(0,0,0,0.06)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    function showDetailSpp(noSurat) {
        document.getElementById('detailNoSurat').innerText = "Rincian Item: " + noSurat;
        document.getElementById('loadingRow').style.display = 'table-row-group';
        document.getElementById('detailItemsBody').innerHTML = '';
        document.getElementById('detailTotalNominal').innerText = 'Rp 0';
        var myModal = new bootstrap.Modal(document.getElementById('modalDetailSpp'));
        myModal.show();
        fetch('/spp/detail-items?no_surat=' + encodeURIComponent(noSurat))
            .then(r => r.json())
            .then(data => {
                document.getElementById('loadingRow').style.display = 'none';
                let html = '';
                let total = 0;
                data.forEach((item, i) => {
                    total += parseFloat(item.nominal ?? 0);
                    html += `<tr><td class="text-center">${i+1}</td><td>${item.kode_budget}</td><td>${item.keterangan ?? '-'}</td><td class="text-end">Rp ${parseFloat(item.nominal ?? 0).toLocaleString('id-ID')}</td></tr>`;
                });
                document.getElementById('detailItemsBody').innerHTML = html;
                document.getElementById('detailTotalNominal').innerText = 'Rp ' + total.toLocaleString('id-ID');
            }).catch(() => {
                document.getElementById('loadingRow').style.display = 'none';
                document.getElementById('detailItemsBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Gagal memuat data.</td></tr>';
            });
    }
</script>
@endpush
@endsection
