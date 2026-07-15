@extends('layouts.app')

@section('title', 'Kelola Surat | B-SMART')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 p-3 rounded-4 d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-check fs-4 me-3 text-success"></i>
            <div class="fw-semibold text-dark">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4 p-3 rounded-4 d-flex align-items-center" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-4 me-3 text-danger"></i>
            <div class="fw-semibold text-dark">{{ session('error') }}</div>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 16px; background: #ffffff;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-clipboard-list text-primary me-2"></i>Kelola Surat (Super Review)</h4>
                    <p class="text-muted small m-0 mt-1">Review semua SPP dengan filter status.</p>
                </div>
            </div>

            <div class="row g-2 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Filter Status</label>
                    <select id="statusFilter" class="form-select form-select-sm" onchange="window.location='{{ url('/spp/kelola') }}?status=' + encodeURIComponent(this.value)">
                        <option value="">Semua Status</option>
                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle" style="border-color: #f1f3f4;">
                    <thead style="background: #f8fafd; color: #5f6368; font-size: 12.5px; font-weight: 600;">
                        <tr>
                            <th width="4%" class="text-center border-0 py-3">No</th>
                            <th width="14%" class="border-0 py-3">No. Surat</th>
                            <th width="10%" class="border-0 py-3">Project</th>
                            <th width="11%" class="border-0 py-3">Tanggal</th>
                            <th width="10%" class="border-0 py-3">Unit / Area</th>
                            <th width="18%" class="border-0 py-3">Lampiran Berkas Secured</th>
                            <th width="14%" class="border-0 py-3">Total Nominal</th>
                            <th width="11%" class="text-center border-0 py-3">Status</th>
                            <th width="13%" class="text-center border-0 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px; color: #3c4043;">
                        @forelse($data as $index => $s)
                            <tr style="border-bottom: 1px solid #f1f3f4;">
                                <td class="text-center fw-semibold text-secondary py-3">{{ $data->firstItem() + $index }}</td>
                                <td>
                                    <a href="javascript:void(0)" class="fw-bold text-dark text-decoration-none" onclick="showDetailSpp('{{ $s->no_surat }}')" title="Klik untuk lihat rincian anggaran">
                                        {{ $s->no_surat }} <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-muted" style="font-size: 10px;"></i>
                                    </a>
                                </td>
                                <td class="text-secondary">{{ $s->kode_project }}</td>
                                <td class="text-secondary">{{ date('d M Y', strtotime($s->tanggal)) }}</td>
                                <td><span class="badge bg-light text-dark border px-2.5 py-1.5" style="border-radius: 6px;">{{ $s->kode_area }}</span></td>
                                <td>
                                    @if(isset($s->files_maker) && $s->files_maker->count() > 0)
                                        <div class="mb-2">
                                            <span class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;">STAF (MAKER):</span>
                                            @foreach($s->files_maker as $fm)
                                                <a href="/spp/file/{{ $fm->nama_file }}" target="_blank" class="d-inline-flex align-items-center text-decoration-none text-primary me-1 mb-1 bg-light border px-2 py-1 rounded" style="font-size: 11px; font-weight: 500;" title="Buka lampiran staf">
                                                    <i class="fa-solid fa-paperclip me-1 text-secondary"></i> Doc-{{ $loop->iteration }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(isset($s->files_checker) && $s->files_checker->count() > 0)
                                        <div>
                                            <span class="text-success d-block mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;">MANAGEMENT (CHECKER):</span>
                                            @foreach($s->files_checker as $fc)
                                                <a href="/spp/file/{{ $fc->nama_file }}" target="_blank" class="d-inline-flex align-items-center text-decoration-none text-success me-1 mb-1 bg-success-subtle border border-success-subtle px-2 py-1 rounded" style="font-size: 11px; font-weight: 500;" title="Buka berkas otorisasi">
                                                    <i class="fa-solid fa-file-shield me-1 text-success"></i> Bukti-{{ $loop->iteration }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if((!isset($s->files_maker) || $s->files_maker->count() == 0) && (!isset($s->files_checker) || $s->files_checker->count() == 0))
                                        <span class="text-muted small px-2 py-1 bg-light rounded text-center d-inline-block" style="font-size: 11px; font-style: italic;">Tidak ada berkas</span>
                                    @endif
                                </td>

                                <td class="fw-bold text-dark">Rp {{ number_format($s->total_nominal, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($s->status_surat == 'Pending' || $s->status_surat == 'Pending Director Otorisasi')
                                        <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1.5" style="font-size: 11px;">{{ $s->status_surat }}</span>
                                    @elseif($s->status_surat == 'Approved')
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1.5" style="font-size: 11px;">Approved</span>
                                    @elseif($s->status_surat == 'Disbursed')
                                        <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1.5" style="font-size: 11px; background-color: #e0f2fe !important; color: #0369a1 !important;">Disbursed</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5" style="font-size: 11px;">Rejected</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        @if((session('role') == $s->posisi_saat_ini || session('role') == 'ADMIN') && str_contains($s->status_surat, 'Pending'))
                                            <button type="button" class="btn btn-sm btn-outline-primary px-3 rounded-pill" style="font-size: 11.5px; font-weight: 500;" onclick="bukaModalValidasi('{{ $s->no_surat }}', '{{ $s->no_surat }}', '{{ number_format($s->total_nominal, 0, ',', '.') }}')">
                                                <i class="fa-solid fa-user-check me-1"></i> Periksa
                                            </button>

                                        @elseif((session('role') == 'KASIR_PUSAT' || session('role') == 'ADMIN') && $s->status_surat == 'Approved' && $s->posisi_saat_ini == 'KASIR_PUSAT')
                                            <form action="/spp/cairkan" method="POST" onsubmit="return confirm('Apakah Anda yakin dana untuk surat {{ $s->no_surat }} ini sudah ditransfer via e-banking dan ingin mencairkannya di sistem?')">
                                                @csrf
                                                <input type="hidden" name="no_surat" value="{{ $s->no_surat }}" />
                                                <button type="submit" class="btn btn-sm btn-success px-3 rounded-pill" style="font-size: 11.5px; font-weight: 600;">
                                                    <i class="fa-solid fa-money-bill-transfer me-1"></i> Cairkan Dana
                                                </button>
                                            </form>

                                        @else
                                            <span class="text-muted small" style="font-style: italic;">
                                                @if($s->status_surat == 'Disbursed')
                                                    <span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Selesai Cair</span>
                                                @else
                                                    <code>Otoritas: {{ $s->posisi_saat_ini }}</code>
                                                @endif
                                            </span>
                                        @endif

                                        @if($s->status_surat == 'Approved' || $s->status_surat == 'Disbursed')
                                            @php $canPrint = in_array(session('role'), ['ADMIN', 'MANAGER_KEUANGAN', 'DIREKTUR', 'FINANCE_PROJECT', 'PROJECT_MANAGER', 'KASIR_PUSAT'], true); @endphp
                                            @if($canPrint)
                                                <button type="button" class="btn btn-sm btn-outline-secondary border text-secondary px-2.5 rounded-pill ms-1" style="font-size: 11.5px;" title="Preview Hasil Cetak SPP" onclick="showPreviewSpp('{{ $s->no_surat }}')">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                                <a href="/spp/cetak?no_surat={{ rawurlencode($s->no_surat) }}" target="_blank" class="btn btn-sm btn-light border text-secondary px-2.5 rounded-pill ms-1" style="font-size: 11.5px;" title="Cetak Bukti Dokumen PDF SPP">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5" style="background: #ffffff;">
                                    <i class="fa-solid fa-folder-open d-block fs-2 mb-2 text-black-50"></i> Tidak ada data SPP sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            <div class="d-flex justify-content-center mt-4">
                {{ $data->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    @push('modals')
    @include('spp.modals')
    @endpush

</div>
@endsection

@push('scripts')
<script>
    // Samakan JS detail preview seperti di spp/index
    function showDetailSpp(noSurat) {
        document.getElementById('detailNoSurat').innerText = "Rincian Item: " + noSurat;
        document.getElementById('loadingRow').style.display = 'table-row-group';
        document.getElementById('detailItemsBody').innerHTML = '';
        document.getElementById('detailTotalNominal').innerText = 'Rp 0';

        var myModal = new bootstrap.Modal(document.getElementById('modalDetailSpp'));
        myModal.show();

        let urlSafeNoSurat = encodeURIComponent(noSurat);

        fetch('/spp/detail-items?no_surat=' + urlSafeNoSurat)
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingRow').style.display = 'none';

                let htmlRows = '';
                let grandTotal = 0;

                if (data.length === 0) {
                    htmlRows = `<tr><td colspan="4" class="text-center text-muted py-4">Tidak ada item rincian dana untuk surat ini.</td></tr>`;
                } else {
                    data.forEach((item, index) => {
                        let nominalBelanja = parseFloat(item.nominal ?? 0);
                        grandTotal += nominalBelanja;

                        htmlRows += `
                            <tr style="border-bottom: 1px solid #f1f3f4;">
                                <td class="text-center text-secondary fw-semibold py-2.5">${index + 1}</td>
                                <td>
                                    <span class="fw-semibold text-dark d-block">${item.kode_budget}</span>
                                    <small class="text-muted" style="font-size: 11px;">${item.nama_budget ?? 'Komponen Anggaran'}</small>
                                </td>
                                <td class="text-secondary">${item.keterangan ?? '-'}</td>
                                <td class="text-end fw-bold text-dark">Rp ${nominalBelanja.toLocaleString('id-ID')}</td>
                            </tr>
                        `;
                    });
                }

                document.getElementById('detailItemsBody').innerHTML = htmlRows;
                document.getElementById('detailTotalNominal').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loadingRow').style.display = 'none';
                document.getElementById('detailItemsBody').innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">Gagal memuat rincian data dari server.</td></tr>`;
            });
    }

    function showPreviewSpp(noSurat) {
        document.getElementById('previewSuratLabel').innerText = "No. Surat: " + noSurat;
        document.getElementById('previewFrame').src = '/spp/preview-cetak?no_surat=' + encodeURIComponent(noSurat);
        document.getElementById('previewPrintLink').href = '/spp/cetak?no_surat=' + encodeURIComponent(noSurat);
        document.getElementById('previewLoading').style.display = 'flex';

        var previewFrame = document.getElementById('previewFrame');
        previewFrame.onload = function() {
            document.getElementById('previewLoading').style.display = 'none';
        };

        var myModal = new bootstrap.Modal(document.getElementById('modalPreviewSpp'));
        myModal.show();
    }
</script>
@endpush



