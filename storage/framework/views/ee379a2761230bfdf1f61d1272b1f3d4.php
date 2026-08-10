<?php $__env->startSection('title', 'Input SPP | CDB Finance - B-SMART'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .btn-add-row:hover {
        background: rgba(26, 115, 232, 0.04);
        border-color: #1a73e8;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center gap-3">
                <i class="fa-solid fa-file-signature fs-4 text-primary"></i>
                <h5 class="fw-bold text-dark m-0" style="font-size:20px;">Form Input Surat Permintaan Pembayaran (SPP)</h5>
            </div>
            <div class="card-body p-4">
                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check me-1"></i><?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-xmark me-1"></i><?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if(session('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i><?php echo e(session('warning')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fa-solid fa-circle-exclamation me-1"></i>Periksa kembali form:</strong>
                        <ul class="mb-0 mt-1">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($err); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <form action="/spp/simpan" method="POST" enctype="multipart/form-data" data-loading>
                    <?php echo csrf_field(); ?> 

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Nomor Surat</label>
                            <input type="text" name="no_surat" class="form-control-custom" value="<?php echo e($nomor_baru); ?>" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-custom">Tanggal Pengajuan</label>
                            <input type="date" name="tanggal" class="form-control-custom" value="<?php echo e(date('Y-m-d')); ?>" required>
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
                            <select name="kode_project" id="projectSelect"
                                class="form-select-custom" required
                                onchange="filterBudgetMaster()"
                                <?php echo e(session('kode_project') && session('kode_project') !== 'all' ? 'disabled' : ''); ?>>
                                <?php if(!session('kode_project') || session('kode_project') === 'all'): ?>
                                    <option value="">-- Pilih Project --</option>
                                <?php endif; ?>
                                <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($p->kode_project); ?>"
                                        <?php echo e(session('kode_project') == $p->kode_project ? 'selected' : ''); ?>>
                                        <?php echo e($p->nama_project); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php if(session('kode_project') && session('kode_project') !== 'all'): ?>
                                <input type="hidden" name="kode_project" value="<?php echo e(session('kode_project')); ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Area Otorisasi</label>
                            <select name="kode_area" class="form-select-custom" required <?php echo e($isAreaLocked ? 'disabled' : ''); ?>>
                                <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($a->kode_area); ?>" <?php echo e(session('kode_area') == $a->kode_area ? 'selected' : ''); ?>><?php echo e($a->nama_area); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php if($isAreaLocked): ?>
                                <input type="hidden" name="kode_area" value="<?php echo e(session('kode_area')); ?>">
                                <small class="text-muted">Area sesuai unit kerja Anda.</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6" id="budgetMasterSection">
                            <label class="form-label-custom">Kode Budget (Master)</label>
                            <select name="kode_budget_master" id="budgetMasterSelect" class="form-select-custom" required onchange="syncBudgetToRows()">
                                <option value="">-- Pilih Kode Budget --</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-none" id="noBudgetSection">
                            <label class="form-label-custom">Kode Budget</label>
                            <input type="text" name="kode_budget_master" class="form-control-custom" value="NO-BUDGET" readonly>
                            <small class="text-muted">Project tanpa budget line. Kode otomatis terisi NO-BUDGET.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Sumber Dana</label>
                            <select name="sumber_dana" class="form-select-custom" required onchange="showSumberDanaDetail(this)">
                                <option value="">-- Pilih Sumber Dana --</option>
                                <?php $__currentLoopData = $sumber_danas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($sd->id_bank_kas); ?>" data-nama="<?php echo e($sd->nama_rekening); ?>" data-norek="<?php echo e($sd->no_rekening); ?>">
                                        <?php echo e($sd->nama_rekening); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div id="detailSumberDana" class="mt-1" style="font-size:11px; line-height:1.3;"></div>
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
                                <tr style="border-bottom: 1px solid #f1f3f4;">
                                    <td class="text-center row-number fw-semibold text-secondary">1</td>
                                    <td><input type="text" name="items[0][keterangan]" class="form-control form-control-custom" placeholder="Isi rincian pengeluaran..." required></td>
                                    <td>
                                        <input type="text" name="items[0][kode_budget]" class="form-control-custom row-budget-input" readonly placeholder="Mengikuti Master">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-transparent border-end-0 px-2" style="font-size:12px; color:#6b7280;">Rp</span>
                                            <input type="text" name="items[0][jumlah]" class="form-control form-control-custom input-jumlah" placeholder="0" required inputmode="numeric" oninput="formatNominal(this); hitungTotalNominal();">
                                        </div>
                                    </td>
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
    const budgetData = <?php echo json_encode($master_budgets, 15, 512) ?>;
    const noBudgetProjects = <?php echo json_encode($noBudgetProjects, 15, 512) ?>;

    function isNoBudgetProject() {
        const project = document.getElementById('projectSelect').value;
        return noBudgetProjects.includes(project);
    }

    function toggleBudgetSection() {
        const noBudget = isNoBudgetProject();
        document.getElementById('budgetMasterSection')?.classList.toggle('d-none', noBudget);
        document.getElementById('noBudgetSection')?.classList.toggle('d-none', !noBudget);
    }

    function showSumberDanaDetail(sel) {
        const opt = sel.options[sel.selectedIndex];
        const div = document.getElementById('detailSumberDana');
        if (opt && opt.value) {
            const nama = opt.getAttribute('data-nama');
            const norek = opt.getAttribute('data-norek');
            div.innerHTML = '<span class="fw-semibold">' + nama + '</span><br><span style="font-size:10px; color:#6b7280;">' + norek + '</span>';
        } else {
            div.innerHTML = '';
        }
    }

    // 1. Fungsi filter dropdown master berdasarkan project yang dipilih
    function filterBudgetMaster() {
        toggleBudgetSection();
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

    <?php if(session('kode_project') && session('kode_project') !== 'all'): ?>
        document.addEventListener('DOMContentLoaded', filterBudgetMaster);
    <?php endif; ?>

    // 2. Sinkronisasi nilai dari Master Dropdown ke semua baris input tabel rincian di bawah secara otomatis
    function syncBudgetToRows() {
        if (isNoBudgetProject()) return;

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
        const str = String(val).replace(/[^0-9]/g, '');
        const num = parseInt(str, 10);
        return isNaN(num) ? 0 : num;
    }

    // Validasi: pastikan budget master dipilih dan cek nominal vs budget ceiling (terserap vs alokasi)
    function validateBudgetMasterAndRows(showAlert) {
        const alertCeiling = document.getElementById('budgetCeilingAlert');
        const alertMasterReq = document.getElementById('budgetMasterRequiredAlert');
        const submitBtn = document.getElementById('submitSppBtn');

        const projectSelected = document.getElementById('projectSelect').value;

        if (noBudgetProjects.includes(projectSelected)) {
            submitBtn.disabled = false;
            alertCeiling?.classList.add('d-none');
            alertMasterReq?.classList.add('d-none');
            return;
        }

        const masterValue = document.getElementById('budgetMasterSelect').value;

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
        const project = document.getElementById('projectSelect').value;
        const isNoBudget = noBudgetProjects.includes(project);
        const budgetVal = isNoBudget ? 'NO-BUDGET' : document.getElementById('budgetMasterSelect').value;
        
        const newRow = document.createElement('tr');
        newRow.style.borderBottom = "1px solid #f1f3f4";
        newRow.innerHTML = `
            <td class="text-center row-number fw-semibold text-secondary">${tableBody.children.length + 1}</td>
            <td><input type="text" name="items[${rowIndex}][keterangan]" class="form-control form-control-custom" placeholder="Isi rincian pengeluaran..." required></td>
            <td>
                <input type="text" name="items[${rowIndex}][kode_budget]" class="form-control-custom row-budget-input" value="${budgetVal}" readonly placeholder="${isNoBudget ? 'No Budget' : 'Mengikuti Master'}">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0 px-2" style="font-size:12px; color:#6b7280;">Rp</span>
                    <input type="text" name="items[${rowIndex}][jumlah]" class="form-control form-control-custom input-jumlah" placeholder="0" required inputmode="numeric" oninput="formatNominal(this); hitungTotalNominal();">
                </div>
            </td>
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

    // 4. Format input nominal: hanya angka, separator ribuan otomatis
    function formatNominal(input) {
        let raw = input.value.replace(/[^0-9]/g, '');
        if (raw === '') { input.value = ''; return; }
        let num = parseInt(raw, 10);
        input.value = num.toLocaleString('id-ID');
    }

    function rawNominal(input) {
        return parseInt(input.value.replace(/[^0-9]/g, '')) || 0;
    }

    // 5. Hitung akumulasi total live
    function hitungTotalNominal() {
        let total = 0;
        const inputs = document.querySelectorAll('.input-jumlah');
        inputs.forEach(input => { total += rawNominal(input); });
        document.getElementById('totalNominalText').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }

    // 6. Bersihkan format sebelum submit
    document.querySelector('form[data-loading]')?.addEventListener('submit', function() {
        document.querySelectorAll('.input-jumlah').forEach(input => {
            input.value = rawNominal(input);
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/spp/tambah.blade.php ENDPATH**/ ?>