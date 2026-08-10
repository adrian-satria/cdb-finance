<?php $__env->startSection('title', 'Ringkasan Keuangan | B-SMART'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-file-invoice text-success me-2"></i>Ringkasan Keuangan</h4>
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

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card card-bsmart">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-calendar me-2"></i>Data Bulanan</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle text-12">
                            <thead class="bg-table-header">
                                <tr>
                                    <th class="border-0 py-2">Bulan</th>
                                    <th class="text-end border-0 py-2">Total SPP</th>
                                    <th class="text-end border-0 py-2">Total Nominal</th>
                                    <th class="text-center border-0 py-2">Pending</th>
                                    <th class="text-center border-0 py-2">Disetujui</th>
                                    <th class="text-center border-0 py-2">Dicairkan</th>
                                    <th class="text-center border-0 py-2">Ditolak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                ?>
                                <?php $__currentLoopData = $monthlyData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="fw-bold"><?php echo e($months[(int)$m->bulan] ?? $m->bulan); ?> <?php echo e($m->tahun); ?></td>
                                    <td class="text-end"><?php echo e(number_format($m->total_spp, 0, ',', '.')); ?></td>
                                    <td class="text-end fw-bold">Rp <?php echo e(number_format($m->total_nominal, 0, ',', '.')); ?></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($m->pending); ?></span></td>
                                    <td class="text-center"><span class="badge bg-success"><?php echo e($m->approved); ?></span></td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($m->disbursed); ?></span></td>
                                    <td class="text-center"><span class="badge bg-danger"><?php echo e($m->rejected); ?></span></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-bsmart">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-project-diagram me-2"></i>Ringkasan Per Project</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle text-12">
                            <thead class="bg-table-header">
                                <tr>
                                    <th class="border-0 py-2">Project</th>
                                    <th class="text-end border-0 py-2">Total SPP</th>
                                    <th class="text-end border-0 py-2">Total Nominal</th>
                                    <th class="text-end border-0 py-2">Terealisasi</th>
                                    <th class="text-end border-0 py-2">% Realisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $projectSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $persen = $p->total_nominal > 0 ? round(($p->total_disbursed / $p->total_nominal) * 100, 1) : 0; ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border"><?php echo e($p->kode_project); ?></span></td>
                                    <td class="text-end"><?php echo e(number_format($p->total_spp, 0, ',', '.')); ?></td>
                                    <td class="text-end fw-bold">Rp <?php echo e(number_format($p->total_nominal, 0, ',', '.')); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format($p->total_disbursed, 0, ',', '.')); ?></td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <div class="progress" style="width: 80px; height: 6px;">
                                                <div class="progress-bar bg-success" style="width: <?php echo e($persen); ?>%"></div>
                                            </div>
                                            <small><?php echo e($persen); ?>%</small>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/reports/financial_summary.blade.php ENDPATH**/ ?>