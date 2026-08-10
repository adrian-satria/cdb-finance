<?php $__env->startSection('title', 'Budget vs Actual | B-SMART'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-chart-bar text-primary me-2"></i>Budget vs Actual</h4>
    </div>

    <div class="card card-bsmart mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Project</label>
                    <select name="kode_project" class="form-select form-select-sm">
                        <option value="">Semua Project</option>
                        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->kode_project); ?>" <?php echo e($kodeProject == $p->kode_project ? 'selected' : ''); ?>><?php echo e($p->kode_project); ?> - <?php echo e($p->nama_project); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        <?php for($y = date('Y'); $y >= 2023; $y--): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e($tahun == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                    <a href="<?php echo e(request()->fullUrlWithQuery(['export' => 'csv'])); ?>" class="btn btn-success btn-sm w-100" style="white-space: nowrap;">
                        <i class="fa-solid fa-download me-1"></i> CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if($summary): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-bsmart">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total Alokasi</div>
                    <div class="fw-bold fs-5 text-dark">Rp <?php echo e(number_format($summary->total_alokasi ?? 0, 0, ',', '.')); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bsmart">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total Terserap</div>
                    <div class="fw-bold fs-5 text-primary">Rp <?php echo e(number_format($summary->total_terserap ?? 0, 0, ',', '.')); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bsmart">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total Sisa</div>
                    <div class="fw-bold fs-5 text-success">Rp <?php echo e(number_format($summary->total_sisa ?? 0, 0, ',', '.')); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bsmart">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total Budget Items</div>
                    <div class="fw-bold fs-5 text-info"><?php echo e($summary->total_budget ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle text-12">
                    <thead class="bg-table-header">
                        <tr>
                            <th class="border-0 py-3">No</th>
                            <th class="border-0 py-3">Project</th>
                            <th class="border-0 py-3">Kode Budget</th>
                            <th class="border-0 py-3">Nama Budget</th>
                            <th class="text-end border-0 py-3">Alokasi</th>
                            <th class="text-end border-0 py-3">Terserap</th>
                            <th class="text-end border-0 py-3">Sisa</th>
                            <th class="text-center border-0 py-3">% Serap</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $budgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="text-secondary"><?php echo e($budgets->firstItem() + $i); ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo e($b->kode_project); ?></span></td>
                            <td><code><?php echo e($b->kode_budget); ?></code></td>
                            <td><?php echo e($b->nama_budget); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($b->alokasi_dana ?? 0, 0, ',', '.')); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($b->terserap ?? 0, 0, ',', '.')); ?></td>
                            <td class="text-end fw-bold <?php echo e(($b->sisa_saldo ?? 0) <= 0 ? 'text-danger' : 'text-success'); ?>">Rp <?php echo e(number_format($b->sisa_saldo ?? 0, 0, ',', '.')); ?></td>
                            <td class="text-center">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar <?php echo e(($b->persentase_serap ?? 0) > 90 ? 'bg-danger' : 'bg-primary'); ?>" style="width: <?php echo e(min($b->persentase_serap ?? 0, 100)); ?>%"></div>
                                </div>
                                <small class="text-muted"><?php echo e($b->persentase_serap ?? 0); ?>%</small>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4"><?php echo e($budgets->links()); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/reports/budget_vs_actual.blade.php ENDPATH**/ ?>