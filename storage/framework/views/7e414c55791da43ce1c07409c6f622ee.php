<?php $__env->startSection('title', 'Dashboard | B-SMART'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <?php if(session('success')): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0">
            <i class="fa-solid fa-gauge-high text-primary me-2"></i>Dashboard
            <small class="text-muted fs-6 fw-normal ms-2"><?php echo e(session('jabatan') ?? session('role')); ?></small>
        </h4>
    </div>

    
    <?php if(session('role') === 'ADMIN' && $projects->isNotEmpty()): ?>
    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-auto">
            <label class="form-label small text-muted mb-1">Filter Project</label>
            <select name="kode_project" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 220px;">
                <option value="">Semua Project</option>
                <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($p->kode_project); ?>" <?php echo e($selectedProject == $p->kode_project ? 'selected' : ''); ?>>
                        <?php echo e($p->kode_project); ?> - <?php echo e($p->nama_project); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <?php if($selectedProject): ?>
        <div class="col-auto">
            <a href="/dashboard" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-xmark me-1"></i>Reset</a>
        </div>
        <?php endif; ?>
    </form>
    <?php endif; ?>

    
    <?php if(session('role') === 'ADMIN' && isset($totalBudget)): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-stat shadow-sm" style="border-left-color: #2563eb;">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Budget</p>
                    <h5 class="fw-bold text-primary m-0">Rp <?php echo e(number_format($totalBudget, 0, ',', '.')); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm" style="border-left-color: #10b981;">
                <div class="card-body">
                    <p class="text-muted small mb-1">Terserap</p>
                    <h5 class="fw-bold text-success m-0">Rp <?php echo e(number_format($totalTerserap, 0, ',', '.')); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm" style="border-left-color: #f59e0b;">
                <div class="card-body">
                    <p class="text-muted small mb-1">Sisa Budget</p>
                    <h5 class="fw-bold text-warning m-0">Rp <?php echo e(number_format($sisaBudget, 0, ',', '.')); ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm" style="border-left-color: <?php echo e($persenUtilisasi > 90 ? '#ef4444' : '#06b6d4'); ?>;">
                <div class="card-body">
                    <p class="text-muted small mb-1">Utilisasi</p>
                    <h5 class="fw-bold m-0 <?php echo e($persenUtilisasi > 90 ? 'text-danger' : 'text-info'); ?>">
                        <?php echo e($persenUtilisasi); ?>%
                        <?php if($persenUtilisasi > 90): ?> <i class="fa-solid fa-triangle-exclamation ms-1"></i> <?php endif; ?>
                    </h5>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if(session('role') === 'ADMIN' && isset($criticalBudgets) && $criticalBudgets->isNotEmpty()): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation fs-5"></i>
        <div>
            <strong>Peringatan!</strong> <?php echo e($criticalBudgets->count()); ?> project dengan utilisasi &ge; 90%:
            <?php $__currentLoopData = $criticalBudgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="badge bg-danger ms-1"><?php echo e($b->kode_project); ?> (<?php echo e($b->persen); ?>%)</span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-stat shadow-sm" style="border-left-color: #f59e0b;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Pending</p>
                            <h3 class="fw-bold text-warning m-0"><?php echo e($countPending ?? 0); ?></h3>
                        </div>
                        <div class="rounded-circle bg-warning-subtle p-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-clock text-warning fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm" style="border-left-color: #10b981;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Approved</p>
                            <h3 class="fw-bold text-success m-0"><?php echo e($countApproved ?? 0); ?></h3>
                        </div>
                        <div class="rounded-circle bg-success-subtle p-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-check-circle text-success fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm" style="border-left-color: #ef4444;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Rejected</p>
                            <h3 class="fw-bold text-danger m-0"><?php echo e($countRejected ?? 0); ?></h3>
                        </div>
                        <div class="rounded-circle bg-danger-subtle p-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-ban text-danger fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm" style="border-left-color: #06b6d4;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Disbursed</p>
                            <h3 class="fw-bold text-info m-0"><?php echo e($countDisbursed ?? 0); ?></h3>
                        </div>
                        <div class="rounded-circle bg-info-subtle p-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-money-bill-wave text-info fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(session('role') === 'ADMIN' && isset($budgetPerProject) && $budgetPerProject->isNotEmpty()): ?>
    <div class="card card-bsmart mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-simple me-2 text-primary"></i>Budget vs Actual per Project</h6>
            <?php $__currentLoopData = $budgetPerProject; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $barColor = $b->persen >= 90 ? 'bg-danger' : ($b->persen >= 75 ? 'bg-warning' : 'bg-primary');
                    $textColor = $b->persen >= 90 ? 'text-danger' : 'text-dark';
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold <?php echo e($textColor); ?>">
                            <?php echo e($b->kode_project); ?>

                            <?php if($b->persen >= 90): ?> <i class="fa-solid fa-triangle-exclamation ms-1"></i> <?php endif; ?>
                        </span>
                        <span class="text-muted">
                            Rp <?php echo e(number_format($b->total_terserap, 0, ',', '.')); ?> / Rp <?php echo e(number_format($b->total_alokasi, 0, ',', '.')); ?> (<?php echo e($b->persen); ?>%)
                        </span>
                    </div>
                    <div class="progress" style="height: 16px; border-radius: 8px;">
                        <div class="progress-bar <?php echo e($barColor); ?>" 
                             style="width: <?php echo e(min($b->persen, 100)); ?>%; font-size: 10px; font-weight: 600;">
                            <?php echo e($b->persen); ?>%
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if(session('role') === 'ADMIN' && isset($monthlyChart) && $monthlyChart->isNotEmpty()): ?>
    <div class="card card-bsmart mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-chart-line me-2 text-success"></i>Tren Pengajuan Bulanan (<?php echo e(date('Y')); ?>)</h6>
                <small class="text-muted">Total SPP: <span class="fw-bold text-dark"><?php echo e($monthlyChart->sum('total')); ?></span></small>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-tasks me-2 text-primary"></i>My Tasks</h6>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => '/spp','size' => 'sm','type' => 'outline-primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => '/spp','size' => 'sm','type' => 'outline-primary']); ?>Lihat Semua <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle" style="font-size: 12.5px;">
                    <thead style="background: #f8fafd;">
                        <tr>
                            <th class="border-0 py-2">No. Surat</th>
                            <th class="border-0 py-2">Project</th>
                            <th class="border-0 py-2">Area</th>
                            <th class="text-end border-0 py-2">Nominal</th>
                            <th class="text-center border-0 py-2">Status</th>
                            <th class="text-center border-0 py-2">Posisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a href="javascript:void(0)" class="fw-bold text-dark text-decoration-none" onclick="showDetailSpp('<?php echo e($s->no_surat); ?>')"><?php echo e($s->no_surat); ?></a></td>
                            <td><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['type' => 'light']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'light']); ?><?php echo e($s->kode_project); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></td>
                            <td><?php echo e($s->kode_area); ?></td>
                            <td class="text-end fw-semibold">Rp <?php echo e(number_format($s->total_nominal, 0, ',', '.')); ?></td>
                            <td class="text-center">
                                <?php
                                    $badgeType = match($s->status_surat) {
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        'Disbursed' => 'primary',
                                        default => 'warning',
                                    };
                                ?>
                                <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['type' => ''.e($badgeType).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => ''.e($badgeType).'']); ?><?php echo e($s->status_surat); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
                            </td>
                            <td class="text-center"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['type' => 'light']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'light']); ?><?php echo e($s->posisi_saat_ini); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '6','icon' => 'fa-regular fa-circle-check','title' => 'Tidak ada task yang perlu ditindaklanjuti.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '6','icon' => 'fa-regular fa-circle-check','title' => 'Tidak ada task yang perlu ditindaklanjuti.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->startPush('modals'); ?>
<?php echo $__env->make('spp.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    var ctx = document.getElementById('monthlyChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php $__currentLoopData = $monthlyChart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> '<?php echo e($mc['bulan']); ?>', <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>],
                datasets: [{
                    label: 'Jumlah SPP',
                    data: [<?php $__currentLoopData = $monthlyChart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($mc['total']); ?>, <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                var data = <?php echo json_encode($monthlyChart, 15, 512) ?>;
                                return 'Nominal: Rp ' + (data[context.dataIndex]?.nominal ?? 0).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: 'rgba(0,0,0,0.06)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // showDetailSpp provided by resources/js/modules/spp.js
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/dashboard/index.blade.php ENDPATH**/ ?>