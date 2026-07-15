@extends('layouts.app')
@section('title', 'Dashboard | B-SMART')
@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0">
            <i class="fa-solid fa-gauge-high text-primary me-2"></i>Dashboard
            <small class="text-muted fs-6 fw-normal ms-2">{{ session('jabatan') ?? session('role') }}</small>
        </h4>
    </div>

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

    @if(isset($totalBudget) && isset($totalTerserap))
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Budget Overview</h6>
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="progress" style="height: 24px; border-radius: 12px;">
                        @php $persen = $totalBudget > 0 ? round(($totalTerserap / $totalBudget) * 100, 1) : 0; @endphp
                        <div class="progress-bar bg-primary" style="width: {{ min($persen, 100) }}%; font-size: 12px; font-weight: 600;">
                            {{ $persen }}%
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted">Alokasi: <span class="fw-bold text-dark">Rp {{ number_format($totalBudget, 0, ',', '.') }}</span></small>
                    <br>
                    <small class="text-muted">Terserap: <span class="fw-bold text-primary">Rp {{ number_format($totalTerserap, 0, ',', '.') }}</span></small>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($monthlyChart) && count($monthlyChart) > 0)
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-chart-line me-2 text-success"></i>Tren Pengajuan Bulanan ({{ date('Y') }})</h6>
                <div class="d-flex gap-3">
                    @php $totalTahun = $monthlyChart->sum('total'); @endphp
                    <small class="text-muted">Total SPP: <span class="fw-bold text-dark">{{ $totalTahun }}</span></small>
                </div>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 14px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-tasks me-2 text-primary"></i>My Tasks</h6>
                <a href="/spp" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
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
                            <td><span class="badge bg-light text-dark border">{{ $s->kode_project }}</span></td>
                            <td>{{ $s->kode_area }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($s->total_nominal, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $s->status_surat === 'Approved' ? 'success' : ($s->status_surat === 'Rejected' ? 'danger' : 'warning') }} bg-opacity-10 text-{{ $s->status_surat === 'Approved' ? 'success' : ($s->status_surat === 'Rejected' ? 'danger' : 'warning') }}">
                                    {{ $s->status_surat }}
                                </span>
                            </td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $s->posisi_saat_ini }}</span></td>
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
                                return 'Nominal: Rp ' + data[context.dataIndex].nominal.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
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
