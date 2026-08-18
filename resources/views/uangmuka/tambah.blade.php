@extends('layouts.app')

@section('title', 'Input UM | CDB Finance - B-SMART')

@section('content')
<style>
    .btn-add-row:hover { background: rgba(26,115,232,0.04); border-color: #1a73e8; }
</style>
<div class="row">
    <div class="col-12">
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center gap-3">
                <i class="fa-solid fa-money-bill-wave fs-4 text-primary"></i>
                <h5 class="fw-bold text-dark m-0" style="font-size:20px;">Form Input Uang Muka (UM)</h5>
            </div>
            <div class="card-body p-4">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-xmark me-1"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fa-solid fa-circle-exclamation me-1"></i>Periksa kembali form:</strong>
                        <ul class="mb-0 mt-1">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <form action="/uang-muka/simpan" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">No. AJU</label>
                            <input type="text" class="form-control-custom" value="{{ $nomor_baru }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Tanggal Pengajuan</label>
                            <input type="date" name="tanggal" class="form-control-custom" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Project</label>
                            <select name="kode_project" class="form-select-custom" required {{ session('kode_project') && session('kode_project') !== 'all' ? 'disabled' : '' }}>
                                @if(!session('kode_project') || session('kode_project') === 'all')<option value="">-- Pilih Project --</option>@endif
                                @foreach($projects as $p)
                                    <option value="{{ $p->kode_project }}" {{ session('kode_project') == $p->kode_project ? 'selected' : '' }}>{{ $p->nama_project }}</option>
                                @endforeach
                            </select>
                            @if(session('kode_project') && session('kode_project') !== 'all')<input type="hidden" name="kode_project" value="{{ session('kode_project') }}">@endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Area</label>
                            <select name="kode_area" class="form-select-custom" required {{ $isAreaLocked ? 'disabled' : '' }}>
                                @foreach($areas as $a)
                                    <option value="{{ $a->kode_area }}" {{ session('kode_area') == $a->kode_area ? 'selected' : '' }}>{{ $a->nama_area }}</option>
                                @endforeach
                            </select>
                            @if($isAreaLocked)<input type="hidden" name="kode_area" value="{{ session('kode_area') }}">@endif
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-custom">Keterangan Umum</label>
                            <input type="text" name="keterangan" class="form-control-custom" placeholder="Keperluan uang muka...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">File Lampiran</label>
                            <input type="file" name="file_lampiran[]" class="form-control-custom" multiple>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark m-0" style="font-size:15px;"><i class="fa-solid fa-list-ol text-primary me-2"></i>Rincian Item</h6>
                        <button type="button" class="btn-add-row" onclick="tambahBaris()"><i class="fa-solid fa-plus me-1"></i> Tambah Baris</button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table align-middle table-bsmart" id="detailTable">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="45%">Keterangan</th>
                                    <th width="25%">Kode Budget</th>
                                    <th width="20%">Jumlah (Rp)</th>
                                    <th width="5%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom:1px solid #f1f3f4;">
                                    <td class="text-center row-number fw-semibold text-secondary">1</td>
                                    <td><input type="text" name="items[0][keterangan]" class="form-control form-control-custom" placeholder="Rincian pengeluaran..." required></td>
                                    <td><input type="text" name="items[0][kode_budget]" class="form-control-custom" placeholder="Kode Budget" required></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-transparent border-end-0 px-2" style="font-size:12px;color:#6b7280;">Rp</span>
                                            <input type="text" name="items[0][jumlah]" class="form-control form-control-custom input-jumlah" placeholder="0" required inputmode="numeric" oninput="formatNominal(this); hitungTotal()">
                                        </div>
                                    </td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusBaris(this)"><i class="fa-solid fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mb-4">
                        <div class="total-box d-flex align-items-center gap-3">
                            <span class="text-secondary small fw-normal text-uppercase">Total:</span>
                            <span id="totalText" class="text-danger fs-5 fw-bold">Rp 0</span>
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="/uang-muka" class="btn-bsmart-secondary">Batal</a>
                        <button type="submit" class="btn-bsmart-primary"><i class="fa-solid fa-paper-plane me-1"></i> Simpan & Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let rowIndex = 1;
function tambahBaris() {
    const body = document.querySelector('#detailTable tbody');
    const tr = document.createElement('tr');
    tr.style.borderBottom = '1px solid #f1f3f4';
    tr.innerHTML = `
        <td class="text-center row-number fw-semibold text-secondary">${body.children.length + 1}</td>
        <td><input type="text" name="items[${rowIndex}][keterangan]" class="form-control form-control-custom" placeholder="Rincian pengeluaran..." required></td>
        <td><input type="text" name="items[${rowIndex}][kode_budget]" class="form-control-custom" placeholder="Kode Budget" required></td>
        <td><div class="input-group input-group-sm"><span class="input-group-text bg-transparent border-end-0 px-2" style="font-size:12px;color:#6b7280;">Rp</span><input type="text" name="items[${rowIndex}][jumlah]" class="form-control form-control-custom input-jumlah" placeholder="0" required inputmode="numeric" oninput="formatNominal(this); hitungTotal()"></div></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusBaris(this)"><i class="fa-solid fa-trash"></i></button></td>`;
    body.appendChild(tr);
    rowIndex++;
}
function hapusBaris(btn) {
    const body = document.querySelector('#detailTable tbody');
    if (body.children.length > 1) {
        btn.closest('tr').remove();
        document.querySelectorAll('.row-number').forEach((td, i) => td.innerText = i + 1);
        hitungTotal();
    } else { alert('Minimal 1 item!'); }
}
function formatNominal(input) {
    let raw = input.value.replace(/[^0-9]/g, '');
    input.value = raw === '' ? '' : parseInt(raw, 10).toLocaleString('id-ID');
}
function hitungTotal() {
    let total = 0;
    document.querySelectorAll('.input-jumlah').forEach(i => { total += parseInt(i.value.replace(/[^0-9]/g, '')) || 0; });
    document.getElementById('totalText').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
}
document.querySelector('form')?.addEventListener('submit', function() {
    document.querySelectorAll('.input-jumlah').forEach(i => { i.value = i.value.replace(/[^0-9]/g, ''); });
});
</script>
@endsection
