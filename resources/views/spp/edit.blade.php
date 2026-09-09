@extends('layouts.app')

@section('title', 'Perbaiki SPP Revisi | Finance Management Demo')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center gap-3">
                <i class="fa-solid fa-pen-to-square fs-4 text-warning"></i>
                <h5 class="fw-bold text-dark m-0" style="font-size:20px;">Perbaiki SPP Revisi</h5>
            </div>
            <div class="card-body p-4">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-xmark me-1"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fa-solid fa-circle-exclamation me-1"></i>Periksa kembali form:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($surat->keterangan_checker)
                    <div class="alert alert-warning" style="border-radius: 12px;">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        <strong>Catatan Revisi:</strong> {{ $surat->keterangan_checker }}
                    </div>
                @endif

                <form action="/spp/update" method="POST" enctype="multipart/form-data" data-loading>
                    @csrf
                    <input type="hidden" name="no_surat" value="{{ $surat->no_surat }}">
                    <input type="hidden" name="kode_project" value="{{ $surat->kode_project }}">
                    <input type="hidden" name="kode_area" value="{{ $surat->kode_area }}">
                    <input type="hidden" name="jenis_permintaan" value="{{ $surat->jenis_permintaan }}">

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Nomor Surat</label>
                            <input type="text" class="form-control-custom" value="{{ $surat->no_surat }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Project</label>
                            <input type="text" class="form-control-custom" value="{{ $surat->kode_project }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Area</label>
                            <input type="text" class="form-control-custom" value="{{ $surat->kode_area }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Tanggal Pengajuan</label>
                            <input type="date" name="tanggal" class="form-control-custom" value="{{ $surat->tanggal->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Bank Tujuan</label>
                            <input type="text" name="bank_tujuan" class="form-control-custom" value="{{ $surat->bank_tujuan }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Nomor Rekening</label>
                            <input type="text" name="no_rekening_tujuan" class="form-control-custom" value="{{ $surat->no_rekening_tujuan }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Nama Pemilik Rekening</label>
                            <input type="text" name="nama_rekening_tujuan" class="form-control-custom" value="{{ $surat->nama_rekening_tujuan }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-custom">File Lampiran Tambahan</label>
                            <input type="file" name="file_lampiran[]" class="form-control-custom" multiple>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark m-0" style="font-size:15px;"><i class="fa-solid fa-list-ol text-primary me-2"></i>Rincian Item Permintaan</h6>
                        <button type="button" class="btn-add-row" onclick="tambahBaris()"><i class="fa-solid fa-plus me-1"></i> Tambah Baris</button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table align-middle table-bsmart" id="detailTable">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="45%">Keterangan Keperluan</th>
                                    <th width="25%">Kode Budget</th>
                                    <th width="20%">Jumlah (Rp)</th>
                                    <th width="5%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($surat->details as $i => $item)
                                <tr>
                                    <td class="text-center row-number fw-semibold text-secondary">{{ $i + 1 }}</td>
                                    <td><input type="text" name="items[{{ $i }}][keterangan]" class="form-control form-control-custom" value="{{ $item->keterangan }}" required></td>
                                    <td><input type="text" name="items[{{ $i }}][kode_budget]" class="form-control-custom" value="{{ $item->kode_budget }}" readonly></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-transparent border-end-0 px-2" style="font-size:12px; color:#6b7280;">Rp</span>
                                            <input type="text" name="items[{{ $i }}][jumlah]" class="form-control form-control-custom input-jumlah" value="{{ number_format($item->nominal, 0, ',', '.') }}" required inputmode="numeric" oninput="formatNominal(this); hitungTotal();">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusBaris(this)"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mb-4">
                        <div class="total-box d-flex align-items-center gap-3">
                            <span class="text-secondary small fw-normal text-uppercase">Total Pengajuan:</span>
                            <span id="totalNominalText" class="text-danger fs-5 fw-bold">Rp {{ number_format($surat->total_nominal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="/spp" class="btn-bsmart-secondary">Batal</a>
                        <button type="submit" class="btn-bsmart-primary">
                            <i class="fa-solid fa-paper-plane me-1"></i> Simpan & Kirim Ulang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function formatNominal(input) {
        let num = String(input.value).replace(/[^0-9]/g, '');
        input.value = num ? new Intl.NumberFormat('id-ID').format(parseInt(num, 10)) : '';
    }

    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.input-jumlah').forEach(input => {
            total += parseInt(String(input.value).replace(/[^0-9]/g, '') || '0', 10);
        });
        document.getElementById('totalNominalText').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }

    function tambahBaris() {
        const tbody = document.querySelector('#detailTable tbody');
        const idx = tbody.rows.length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-center row-number fw-semibold text-secondary">${idx + 1}</td>
            <td><input type="text" name="items[${idx}][keterangan]" class="form-control form-control-custom" placeholder="Isi rincian pengeluaran..." required></td>
            <td><input type="text" name="items[${idx}][kode_budget]" class="form-control-custom" value="{{ $surat->details->first()->kode_budget }}" readonly></td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 px-2" style="font-size:12px; color:#6b7280;">Rp</span>
                    <input type="text" name="items[${idx}][jumlah]" class="form-control form-control-custom input-jumlah" placeholder="0" required inputmode="numeric" oninput="formatNominal(this); hitungTotal();">
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusBaris(this)"><i class="fa-solid fa-trash"></i></button>
            </td>`;
        tbody.appendChild(tr);
        renumber();
    }

    function hapusBaris(btn) {
        const tbody = document.querySelector('#detailTable tbody');
        if (tbody.rows.length <= 1) return;
        btn.closest('tr').remove();
        renumber();
        hitungTotal();
    }

    function renumber() {
        document.querySelectorAll('#detailTable tbody tr').forEach((tr, i) => {
            tr.querySelector('.row-number').textContent = i + 1;
        });
    }
</script>
@endsection

