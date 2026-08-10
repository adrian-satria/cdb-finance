<?php $__env->startSection('title', 'Performa Area | B-SMART'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-map-location-dot text-info me-2"></i>Performa Area</h4>
    </div>

    <div class="card card-bsmart mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        <?php for($y = date('Y'); $y >= 2023; $y--): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e($tahun == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted">Project</label>
                    <select name="kode_project" class="form-select form-select-sm">
                        <option value="">Semua Project</option>
                        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->kode_project); ?>" <?php echo e(($kodeProject ?? '') == $p->kode_project ? 'selected' : ''); ?>><?php echo e($p->kode_project); ?> - <?php echo e($p->nama_project); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Dari Tgl</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo e(request('date_from')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Sampai Tgl</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo e(request('date_to')); ?>">
                </div>
                <div class="col-md-3 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                    <a href="<?php echo e(request()->fullUrlWithQuery(['export' => 'csv'])); ?>" class="btn btn-success btn-sm w-100" style="white-space: nowrap;">
                        <i class="fa-solid fa-download me-1"></i> CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle text-12">
                    <thead class="bg-table-header">
                        <tr>
                            <th class="border-0 py-3">#</th>
                            <th class="border-0 py-3">Area</th>
                            <th class="text-end border-0 py-3">Total SPP</th>
                            <th class="text-end border-0 py-3">Total Nominal</th>
                            <th class="text-end border-0 py-3">Rata-rata</th>
                            <th class="text-end border-0 py-3">Terealisasi</th>
                            <th class="text-end border-0 py-3">% Realisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $areaData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $persen = $a->total_nominal > 0 ? round(($a->realized / $a->total_nominal) * 100, 1) : 0; ?>
                        <tr>
                            <td class="text-secondary"><?php echo e($i + 1); ?></td>
                            <td class="fw-bold"><?php echo e($a->kode_area); ?></td>
                            <td class="text-end"><?php echo e(number_format($a->total_spp, 0, ',', '.')); ?></td>
                            <td class="text-end fw-bold">Rp <?php echo e(number_format($a->total_nominal, 0, ',', '.')); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($a->rata_rata, 0, ',', '.')); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($a->realized, 0, ',', '.')); ?></td>
                            <td class="text-end">
                                <span class="badge bg-<?php echo e($persen > 75 ? 'success' : ($persen > 50 ? 'warning' : 'danger')); ?>"><?php echo e($persen); ?>%</span>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/reports/area_performance.blade.php ENDPATH**/ ?>