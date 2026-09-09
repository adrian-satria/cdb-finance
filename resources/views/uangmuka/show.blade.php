@extends('layouts.app')

@section('title', 'Detail UM | Finance Management Demo')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-money-bill-wave fs-4 text-primary"></i>
                    <h5 class="fw-bold text-dark m-0" style="font-size:20px;">Detail Uang Muka {{ $um->no_aju }}</h5>
                </div>
                <a href="/uang-muka/cetak?no_aju={{ $um->no_aju }}" class="btn-bsmart-secondary" target="_blank">
                    <i class="fa-solid fa-print me-1"></i> Cetak PDF
                </a>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fa-solid fa-circle-xmark me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                <div class="row g-3 mb-3">
                    <div class="col-md-3"><label class="form-label-custom">Status</label><br>
                        @php $badge = match($um->status_um){'Pending'=>'bg-warning text-dark','Approved'=>'bg-success','Cair'=>'bg-primary','Revisi'=>'bg-secondary','Rejected'=>'bg-danger',default=>'bg-light text-dark'}; @endphp
                        <span class="badge {{ $badge }} fs-6">{{ $um->status_um }}</span>
                        @if($um->status_um === 'Cair')<span class="badge bg-info text-dark">Posisi: {{ $um->posisi_saat_ini }}</span>@endif
                    </div>
                    <div class="col-md-3"><label class="form-label-custom">Tanggal</label><p class="mb-0 fw-semibold">{{ $um->tanggal?->format('d/m/Y') }}</p></div>
                    <div class="col-md-3"><label class="form-label-custom">Project</label><p class="mb-0 fw-semibold">{{ $um->kode_project }}</p></div>
                    <div class="col-md-3"><label class="form-label-custom">Area</label><p class="mb-0 fw-semibold">{{ $um->kode_area }}</p></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><label class="form-label-custom">Total UM</label><p class="mb-0 fw-semibold">Rp {{ number_format($um->total_nominal, 0, ',', '.') }}</p></div>
                    <div class="col-md-4"><label class="form-label-custom">Sisa LPJ (Piutang)</label><p class="mb-0 fw-semibold text-danger">Rp {{ number_format($um->sisa_lpj, 0, ',', '.') }}</p></div>
                    <div class="col-md-4"><label class="form-label-custom">Batas LPJ</label><p class="mb-0 fw-semibold">{{ $um->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</p></div>
                </div>

                @if($um->status_um === 'Cair' && $um->sisa_lpj > 0)
                    <a href="/uang-muka/lpj/tambah?no_aju={{ $um->no_aju }}" class="btn-bsmart-primary mb-3"><i class="fa-solid fa-clipboard-check me-1"></i> Buat LPJ</a>
                @endif

                @if($um->refund_jumlah > 0)
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>
                            <strong>Setor balik tercatat:</strong> Rp {{ number_format($um->refund_jumlah, 0, ',', '.') }}
                            @if($um->refund_tanggal) ({{ $um->refund_tanggal->format('d/m/Y') }})@endif
                            @if($um->refund_bukti) — <a href="/uang-muka/file/{{ $um->refund_bukti }}" target="_blank">lihat bukti</a>@endif
                        </div>
                    </div>
                @endif

                <div class="table-responsive mb-3">
                    <table class="table align-middle table-bsmart">
                        <thead><tr><th>Uraian</th><th>Kode Budget</th><th class="text-end">Jumlah</th></tr></thead>
                        <tbody>
                            @foreach($um->details as $d)
                                <tr><td>{{ $d->keterangan }}</td><td>{{ $d->kode_budget }}</td><td class="text-end">Rp {{ number_format($d->nominal, 0, ',', '.') }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($um->files->count())
                    <label class="form-label-custom">Lampiran</label>
                    <ul class="mb-3">
                        @foreach($um->files as $f)
                            <li><a href="/uang-muka/file/{{ $f->nama_file }}" target="_blank">{{ $f->nama_file }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if($canAct)
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4"><h6 class="fw-bold m-0">Tindakan</h6></div>
            <div class="card-body p-4">
                @if($um->status_um === 'Approved' && session('role') === 'KASIR_PUSAT')
                    <form action="/uang-muka/cairkan" method="POST" class="mb-2">
                        @csrf
                        <input type="hidden" name="no_aju" value="{{ $um->no_aju }}">
                        <button type="submit" class="btn-bsmart-primary w-100"><i class="fa-solid fa-money-bill-trend-up me-1"></i> Cairkan UM</button>
                    </form>
                @endif
                <form action="/uang-muka/validasi" method="POST">
                    @csrf
                    <input type="hidden" name="no_aju" value="{{ $um->no_aju }}">
                    <div class="mb-2">
                        <label class="form-label-custom">Aksi</label>
                        <select name="aksi" class="form-select-custom" required>
                            <option value="approve">Setuju</option>
                            <option value="revise">Revisi</option>
                            <option value="reject">Tolak</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="alasan" class="form-control-custom" placeholder="Alasan (jika revisi/tolak)">
                    </div>
                    <button type="submit" class="btn-bsmart-primary w-100"><i class="fa-solid fa-check me-1"></i> Proses</button>
                </form>
            </div>
        </div>
        @endif

        @php $refundRoles = config('um_workflow.refund_roles', ['MANAGER_KEUANGAN', 'KASIR_PUSAT']); @endphp
        @if($um->status_um === 'Cair' && $um->sisa_lpj > 0 && in_array(session('role'), $refundRoles))
        <div class="card card-bsmart mt-3">
            <div class="card-header bg-transparent border-0 pt-4 px-4"><h6 class="fw-bold m-0">Setor Balik (Refund)</h6></div>
            <div class="card-body p-4">
                <form action="/uang-muka/refund" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="no_aju" value="{{ $um->no_aju }}">
                    <div class="mb-2">
                        <label class="form-label-custom">Nominal Setor</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-end-0 px-2" style="font-size:12px;color:#6b7280;">Rp</span>
                            <input type="number" name="nominal" class="form-control form-control-custom" value="{{ $um->sisa_lpj }}" max="{{ $um->sisa_lpj }}" required>
                        </div>
                        <small class="text-muted">Sisa piutang: Rp {{ number_format($um->sisa_lpj, 0, ',', '.') }}</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label-custom">Tanggal Setor</label>
                        <input type="date" name="refund_tanggal" class="form-control-custom" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label-custom">Bukti Transfer</label>
                        <input type="file" name="file_lampiran[]" class="form-control-custom" multiple>
                    </div>
                    <button type="submit" class="btn-bsmart-primary w-100"><i class="fa-solid fa-money-bill-transfer me-1"></i> Catat Setor Balik</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

