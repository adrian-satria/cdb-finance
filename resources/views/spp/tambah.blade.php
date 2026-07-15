@extends('layouts.app')

@section('title', 'Input SPP | CDB Finance - B-SMART')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background-color: #f8fafd !important; /* Latar belakang kanvas lembut khas Google */
    }
    .stitch-card { 
        background: #ffffff; 
        border: none !important; 
        border-radius: 24px !important; 
        box-shadow: 0 4px 20px rgba(26, 115, 232, 0.02) !important; 
        margin-bottom: 24px; 
    }
    .stitch-card-header { 
        padding: 24px 24px 12px 24px; 
        border-bottom: none !important; 
        display: flex; 
        align-items: center; 
        gap: 12px; 
    }
    .stitch-card-title { 
        color: #1f1f1f; 
        font-size: 20px; 
        font-weight: 600; 
        letter-spacing: -0.3px; 
        margin: 0; 
    }
    .stitch-card-body { 
        padding: 24px; 
    }
    .form-label-custom { 
        font-size: 13px; 
        font-weight: 500; 
        color: #5f6368; 
        margin-bottom: 8px; 
        display: block; 
    }
    .form-control-custom, .form-select-custom { 
        font-size: 14px; 
        color: #3c4043; 
        background-color: #ffffff; 
        border: 1px solid #e1e3e5 !important; 
        border-radius: 12px !important; /* Super rounded khas Stitch */
        padding: 10px 14px; 
        width: 100%; 
        transition: all 0.2s ease-in-out;
    }
    .form-control-custom:focus, .form-select-custom:focus {
        border-color: #1a73e8 !important;
        box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.15) !important;
        outline: none;
    }
    .form-control-custom[readonly] { 
        background-color: #f1f3f4 !important; 
        cursor: not-allowed; 
        color: #5f6368; 
    }
    .form-divider { 
        border: 0; 
        border-top: 1px dashed #e1e3e5; 
        margin: 28px 0; 
    }
    
    .btn-bsmart-primary { 
        background: #1a73e8 !important; 
        color: #fff !important; 
        font-weight: 600; 
        padding: 12px 28px; 
        border: none; 
        border-radius: 100px !important; /* Pill-shaped button */
        box-shadow: 0 2px 6px rgba(26,115,232,0.15); 
        cursor: pointer; 
        transition: background-color 0.2s;
    }
    .btn-bsmart-primary:hover {
        background-color: #1557b0 !important;
    }
    .btn-bsmart-secondary { 
        background: #ffffff; 
        color: #5f6368; 
        font-weight: 600; 
        padding: 12px 28px; 
        border: 1px solid #e1e3e5; 
        border-radius: 100px !important; 
        cursor: pointer; 
        margin-right: 8px; 
        transition: background-color 0.2s;
    }
    .btn-bsmart-secondary:hover {
        background-color: #f8f9fa;
    }
    .btn-add-row {
        background: transparent;
        color: #1a73e8;
        border: 1px solid #e1e3e5;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 100px;
        font-size: 13px;
        transition: all 0.2s;
    }
    .btn-add-row:hover {
        background: rgba(26, 115, 232, 0.04);
        border-color: #1a73e8;
    }
    .total-box { 
        background-color: rgba(26, 115, 232, 0.06); 
        border-radius: 14px; 
        padding: 12px 24px; 
        font-weight: 700; 
        font-size: 16px; 
        color: #1a73e8; 
    }
    .stitch-table thead {
        background-color: #f8fafd !important;
        color: #5f6368;
        font-size: 13px;
    }
    .stitch-table th, .stitch-table td {
        padding: 14px 12px !important;
        border-color: #f1f3f4 !important;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="stitch-card">
            <div class="stitch-card-header">
                <i class="fa-solid fa-file-signature fs-4 text-primary"></i>
                <h5 class="stitch-card-title">Form Input Surat Permintaan Pembayaran (SPP)</h5>
            </div>
            <div class="stitch-card-body">
                <form action="/spp/simpan" method="POST" enctype="multipart/form-data">
                    @csrf 

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Nomor Surat</label>
                            <input type="text" name="no_surat" class="form-control-custom" value="{{ $nomor_baru }}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-custom">Tanggal Pengajuan</label>
                            <input type="date" name="tanggal" class="form-control-custom" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Jenis Permintaan</label>
                            <select name="jenis_permintaan" class="form-select-custom">
                                <option value="SPP">SPP (Surat Permintaan Pembayaran)</option>
                                <option value="UM">UM (Uang Muka)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Project (Program)</label>
                            <select name="kode_project" id="projectSelect" class="form-select-custom" required onchange="filterBudgetMaster()">
                                <option value="">-- Pilih Project --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->kode_project }}">{{ $p->nama_project }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Area Otorisasi</label>
                            <select name="kode_area" class="form-select-custom" required>
                                @foreach($areas as $a)
                                    <option value="{{ $a->kode_area }}" {{ session('kode_area') == $a->kode_area ? 'selected' : '' }}>{{ $a->nama_area }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Kode Budget (Master)</label>
                            <select name="kode_budget_master" id="budgetMasterSelect" class="form-select-custom" required onchange="syncBudgetToRows()">
                                <option value="">-- Pilih Kode Budget --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Sumber Dana</label>
                            <select name="sumber_dana" class="form-select-custom" required>
                                <option value="">-- Pilih Sumber Dana --</option>
                                @foreach($sumber_danas as $sd)
                                    <option value="{{ $sd->nama_bank }}">{{ $sd->nama_bank }} (Ref: {{ $sd->no_rekening }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-2">
                            <label class="form-label-custom">Bank Tujuan</label>
                            <input type="text" name="bank_tujuan" class="form-control-custom" placeholder="Contoh: BCA" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Nomor Rekening</label>
                            <input type="text" name="no_rekening_tujuan" class="form-control-custom" placeholder="000000xxxx" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Nama Pemilik Rekening</label>
                            <input type="text" name="nama_rekening_tujuan" class="form-control-custom" placeholder="Nama lengkap sesuai buku tabungan" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">File Lampiran Berkas (Bisa Banyak File)</label>
                            <input type="file" name="file_lampiran[]" class="form-control-custom" multiple>
                            <span class="text-muted d-block mt-1" style="font-size: 11px;">* Bisa pilih lebih dari 1 file sekaligus (Tahan Ctrl + Klik File).</span>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark m-0" style="font-size:15px;"><i class="fa-solid fa-list-ol text-primary me-2"></i>Rincian Item Permintaan</h6>
                        <button type="button" class="btn-add-row" onclick="tambahBarisTabel()"><i class="fa-solid fa-plus me-1"></i> Tambah Baris</button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table align-middle stitch-table" id="detailTable">
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
                                <tr style="border-bottom: 1px solid #f1f3f4;">
                                    <td class="text-center row-number fw-semibold text-secondary">1</td>
                                    <td><input type="text" name="items[0][keterangan]" class="form-control form-control-custom" placeholder="Isi rincian pengeluaran..." required></td>
                                    <td>
                                        <input type="text" name="items[0][kode_budget]" class="form-control-custom row-budget-input" readonly placeholder="Mengikuti Master">
                                    </td>
                                    <td><input type="number" name="items[0][jumlah]" class="form-control form-control-custom input-jumlah" placeholder="0" required oninput="hitungTotalNominal()"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusBarisTabel(this)"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mb-4">
                        <div class="total-box d-flex align-items-center gap-3">
                            <span class="text-secondary small fw-normal text-uppercase">Total Pengajuan:</span>
                            <span id="totalNominalText" class="text-danger fs-5 fw-bold">Rp 0</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="alert alert-danger small d-none" id="budgetCeilingAlert" role="alert" style="border-radius: 12px;"></div>
                        <div class="alert alert-warning small d-none" id="budgetMasterRequiredAlert" role="alert" style="border-radius: 12px;"></div>
                        <div class="mt-2 text-end">
                            <button type="reset" class="btn-bsmart-secondary" onclick="setTimeout(() => { hitungTotalNominal(); syncBudgetToRows(); validateBudgetMasterAndRows(true); }, 50)">Reset Form</button>
                            <button type="submit" class="btn-bsmart-primary" id="submitSppBtn" disabled>
                                <i class="fa-solid fa-paper-plane me-1"></i> Simpan & Kirim Pengajuan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Pastikan data dari Laravel diubah menjadi JSON Object yang valid
    const budgetData = @json($master_budgets);

    // 1. Fungsi filter dropdown master berdasarkan project yang dipilih
    function filterBudgetMaster() {
        const projectSelected = document.getElementById('projectSelect').value;
        const budgetMaster = document.getElementById('budgetMasterSelect');

        budgetMaster.innerHTML = '<option value="">-- Pilih Kode Budget --</option>';

        // Saat project berubah, reset semua kode_budget di baris agar tidak "terbawa" nilai sebelumnya.
        const rowInputs = document.querySelectorAll('.row-budget-input');
        rowInputs.forEach(input => {
            input.value = '';
        });

        // Membaca data dari DB menggunakan properti asli: 'kode_budget' dan 'nama_budget'
        if (budgetData[projectSelected]) {
            budgetData[projectSelected].forEach(item => {
                let optMaster = new Option(`${item.kode_budget} - ${item.nama_budget}`, item.kode_budget);
                budgetMaster.add(optMaster);
            });
        }
        // Jalankan sinkronisasi (akan NO-OP bila master belum dipilih)
        syncBudgetToRows();
    }

    // 2. Sinkronisasi nilai dari Master Dropdown ke semua baris input tabel rincian di bawah secara otomatis
    function syncBudgetToRows() {
        const masterValue = document.getElementById('budgetMasterSelect').value;
        const rowInputs = document.querySelectorAll('.row-budget-input');

        // Guard: jangan menimpa kode_budget di row kalau master belum dipilih.
        if (!masterValue) return;

        rowInputs.forEach(input => {
            input.value = masterValue;
        });

        validateBudgetMasterAndRows(false);
    }

    function moneyToInt(val) {
        if (val === null || val === undefined) return 0;
        const str = String(val).replace(/[^0-9.-]/g, '');
        const num = parseFloat(str);
        return isNaN(num) ? 0 : num;
    }

    // Validasi: pastikan budget master dipilih dan cek nominal vs budget ceiling (terserap vs alokasi)
    function validateBudgetMasterAndRows(showAlert) {
        const alertCeiling = document.getElementById('budgetCeilingAlert');
        const alertMasterReq = document.getElementById('budgetMasterRequiredAlert');
        const submitBtn = document.getElementById('submitSppBtn');

        const masterValue = document.getElementById('budgetMasterSelect').value;
        const projectSelected = document.getElementById('projectSelect').value;

        const rows = document.querySelectorAll('#detailTable tbody tr');
        let anyInvalid = false;
        let invalidMessage = '';

        if (!masterValue) {
            anyInvalid = true;
            invalidMessage = 'Pilih Kode Budget (Master) terlebih dahulu sebelum menambah/submit item.';
        } else {
            // cek tiap baris: kode_budget wajib terisi (readonly harusnya terisi, tapi harden)
            for (let tr of rows) {
                const kodeBudget = tr.querySelector('.row-budget-input')?.value;
                const jumlah = tr.querySelector('.input-jumlah')?.value;
                if (!kodeBudget) {
                    anyInvalid = true;
                    invalidMessage = 'Ada baris item yang belum memiliki Kode Budget. Pastikan master budget sudah dipilih.';
                    break;
                }
                // nominal akan divalidasi server juga, tapi supaya user dapat alert lebih cepat
                const jumlahNum = moneyToInt(jumlah);

                const budgetsForProject = budgetData?.[projectSelected] || [];
                const masterRow = budgetsForProject.find(b => String(b.kode_budget) === String(kodeBudget));

                if (!masterRow) {
                    // kalau masterRow tidak ketemu berarti data budget master tidak sesuai project
                    anyInvalid = true;
                    invalidMessage = 'Kode Budget [ ' + kodeBudget + ' ] tidak ditemukan di master. Silakan pilih ulang Project & Master Budget.';
                    break;
                }

                // alokasi_dana & terserap dari master_budget (angka/digit/decimal)
                const sisaSaldo = moneyToInt(masterRow.alokasi_dana) - moneyToInt(masterRow.terserap);
                if (jumlahNum > sisaSaldo + 1e-9) {
                    anyInvalid = true;
                    invalidMessage = 'Nominal item melebihi sisa saldo budget. Sisa untuk [' + (masterRow.nama_budget || kodeBudget) + '] adalah ' + sisaSaldo.toLocaleString('id-ID') + '.';
                    break;
                }
            }
        }

        if (anyInvalid) {
            submitBtn.disabled = true;
            if (alertMasterReq) {
                if (!masterValue) {
                    alertMasterReq.innerText = invalidMessage;
                    alertMasterReq.classList.remove('d-none');
                    alertCeiling?.classList.add('d-none');
                } else {
                    alertCeiling && (() => { alertCeiling.innerText = invalidMessage; alertCeiling.classList.remove('d-none'); })();
                    alertMasterReq?.classList.add('d-none');
                }
            } else if (alertCeiling) {
                alertCeiling.innerText = invalidMessage;
                alertCeiling.classList.remove('d-none');
            }

            if (showAlert) alert(invalidMessage);
        } else {
            submitBtn.disabled = false;
            alertCeiling?.classList.add('d-none');
            alertMasterReq?.classList.add('d-none');
        }
    }


    let rowIndex = 1;
    // 3. Tambah Baris Baru (Otomatis langsung mengambil isi dari Budget Master yang sedang aktif)
    function tambahBarisTabel() {
        const tableBody = document.querySelector('#detailTable tbody');
        const masterValue = document.getElementById('budgetMasterSelect').value;
        
        const newRow = document.createElement('tr');
        newRow.style.borderBottom = "1px solid #f1f3f4";
        newRow.innerHTML = `
            <td class="text-center row-number fw-semibold text-secondary">${tableBody.children.length + 1}</td>
            <td><input type="text" name="items[${rowIndex}][keterangan]" class="form-control form-control-custom" placeholder="Isi rincian pengeluaran..." required></td>
            <td>
                <input type="text" name="items[${rowIndex}][kode_budget]" class="form-control-custom row-budget-input" value="${masterValue}" readonly placeholder="Mengikuti Master">
            </td>
            <td><input type="number" name="items[${rowIndex}][jumlah]" class="form-control form-control-custom input-jumlah" placeholder="0" required oninput="hitungTotalNominal()"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusBarisTabel(this)"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tableBody.appendChild(newRow);
        rowIndex++;
    }

    function hapusBarisTabel(button) {
        const tableBody = document.querySelector('#detailTable tbody');
        if (tableBody.children.length > 1) {
            button.closest('tr').remove();
            document.querySelectorAll('.row-number').forEach((td, i) => {
                td.innerText = i + 1;
            });
            hitungTotalNominal();
        } else {
            alert('Minimal harus ada 1 item rincian dana!');
        }
    }

    // 4. Hitung akumulasi total live
    function hitungTotalNominal() {
        let total = 0;
        const inputs = document.querySelectorAll('.input-jumlah');
        
        inputs.forEach(input => {
            let nilai = parseFloat(input.value) || 0;
            total += nilai;
        });

        document.getElementById('totalNominalText').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }
</script>
@endsection