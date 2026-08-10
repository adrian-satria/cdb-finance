<?php $__env->startSection('title', 'Budget per Area | CDB Finance'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary m-0">
            <i class="fa-solid fa-location-dot me-2"></i>Budget per Area
        </h4>
        <a href="<?php echo e(route('admin.budget.index')); ?>" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card card-bsmart mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted">Kode Budget</small>
                    <div class="fw-bold"><?php echo e($master->kode_budget); ?></div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Nama Budget</small>
                    <div class="fw-bold"><?php echo e($master->nama_budget); ?></div>
                </div>
                <div class="col-md-2">
                    <small class="text-muted">Project</small>
                    <div class="fw-bold"><?php echo e($master->kode_project); ?></div>
                </div>
                <div class="col-md-3 text-end">
                    <small class="text-muted">Alokasi Master</small>
                    <div class="fw-bold text-primary fs-5">Rp <?php echo e(number_format($master->alokasi_dana, 0, ',', '.')); ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <form method="POST" action="<?php echo e(route('admin.budget.area.store', $master->id_budget)); ?>" id="areaForm">
                <?php echo csrf_field(); ?>

                <div class="table-responsive">
                    <table class="table align-middle table-bsmart">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center border-0 py-3">No</th>
                                <th width="25%" class="border-0 py-3">Area</th>
                                <th width="25%" class="text-end border-0 py-3">Alokasi (Rp)</th>
                                <th width="20%" class="text-end border-0 py-3">Terserap (Rp)</th>
                                <th width="20%" class="text-end border-0 py-3">Sisa (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sumAlokasi = 0; $sumTerserap = 0; ?>
                            <?php $__empty_1 = true; $__currentLoopData = $projectAreas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $ba = $budgetAreas->get($area->kode_area);
                                $alokasi = $ba ? (float) $ba->alokasi_dana : 0;
                                $terserap = $ba ? (float) $ba->terserap : 0;
                                $sisa = $alokasi - $terserap;
                                $sumAlokasi += $alokasi;
                                $sumTerserap += $terserap;
                            ?>
                            <tr style="border-bottom: 1px solid #f1f3f4;">
                                <td class="text-center fw-semibold text-secondary py-3"><?php echo e($i + 1); ?></td>
                                <td>
                                    <strong><?php echo e(strtoupper($area->kode_area)); ?></strong>
                                    <small class="text-muted d-block"><?php echo e($area->nama_area); ?></small>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white">Rp</span>
                                        <input type="text" name="alokasi[<?php echo e($area->kode_area); ?>]"
                                            class="form-control form-control-sm text-end fw-bold alokasi-input"
                                            value="<?php echo e(number_format($alokasi, 0, ',', '')); ?>"
                                            oninput="formatAngka(this); hitungTotal();">
                                    </div>
                                </td>
                                <td class="text-end fw-semibold py-3">
                                    Rp <?php echo e(number_format($terserap, 0, ',', '.')); ?>

                                </td>
                                <td class="text-end fw-bold py-3 <?php echo e($sisa < 0 ? 'text-danger' : ($sisa == 0 ? 'text-warning' : 'text-success')); ?>">
                                    Rp <?php echo e(number_format($sisa, 0, ',', '.')); ?>

                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Project ini belum memiliki area. <a href="<?php echo e(route('admin.project.edit', $master->kode_project)); ?>">Atur area di sini</a>.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end fw-bold py-3">Total Area</td>
                                <td class="text-end fw-bold py-3" id="totalAlokasi">Rp <?php echo e(number_format($sumAlokasi, 0, ',', '.')); ?></td>
                                <td class="text-end fw-bold py-3">Rp <?php echo e(number_format($sumTerserap, 0, ',', '.')); ?></td>
                                <td class="text-end fw-bold py-3" id="totalSisa">Rp <?php echo e(number_format($sumAlokasi - $sumTerserap, 0, ',', '.')); ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end fw-bold py-2 text-primary">Master Budget</td>
                                <td class="text-end fw-bold py-2 text-primary">Rp <?php echo e(number_format($master->alokasi_dana, 0, ',', '.')); ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-regular fa-floppy-disk me-1"></i> Simpan
                    </button>
                    <a href="<?php echo e(route('admin.budget.index')); ?>" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function formatAngka(input) {
    let raw = input.value.replace(/[^0-9]/g, '');
    if (raw === '') { input.value = '0'; return; }
    input.value = parseInt(raw).toLocaleString('id-ID');
}

function hitungTotal() {
    let total = 0;
    document.querySelectorAll('.alokasi-input').forEach(inp => {
        total += parseInt(inp.value.replace(/[^0-9]/g, '') || 0);
    });
    document.getElementById('totalAlokasi').innerText = 'Rp ' + total.toLocaleString('id-ID');

    const master = <?php echo e($master->alokasi_dana); ?>;
    const sisa = master - total;
    const el = document.getElementById('totalSisa');
    el.innerText = 'Rp ' + Math.abs(sisa).toLocaleString('id-ID');
    el.className = 'text-end fw-bold py-3 ' + (sisa < 0 ? 'text-danger' : (sisa === 0 ? 'text-warning' : 'text-success'));
}

document.getElementById('areaForm')?.addEventListener('submit', function() {
    document.querySelectorAll('.alokasi-input').forEach(inp => {
        inp.value = inp.value.replace(/[^0-9]/g, '') || '0';
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/budget/area.blade.php ENDPATH**/ ?>