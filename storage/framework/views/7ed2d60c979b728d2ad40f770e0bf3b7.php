<?php $__env->startSection('title', 'Monitor Aktivitas | B-SMART'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-activity text-danger me-2"></i>Monitor Aktivitas User</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-bsmart">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total Aktivitas Hari Ini</div>
                    <div class="fw-bold fs-4 text-dark"><?php echo e($todayStats->total ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bsmart">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">User Aktif Hari Ini</div>
                    <div class="fw-bold fs-4 text-primary"><?php echo e($todayStats->unique_users ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-bsmart">
                <div class="card-body text-center py-3">
                    <div class="text-muted small">Total User Terdaftar</div>
                    <div class="fw-bold fs-4 text-success"><?php echo e($activeUsers->count()); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-bsmart mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Username</label>
                    <input type="text" name="username" class="form-control form-control-sm" value="<?php echo e(request('username')); ?>" placeholder="Cari username...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Aktivitas</label>
                    <select name="aktivitas" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="login" <?php echo e(request('aktivitas') === 'login' ? 'selected' : ''); ?>>Login</option>
                        <option value="logout" <?php echo e(request('aktivitas') === 'logout' ? 'selected' : ''); ?>>Logout</option>
                        <option value="create_spp" <?php echo e(request('aktivitas') === 'create_spp' ? 'selected' : ''); ?>>Create SPP</option>
                        <option value="approve_spp" <?php echo e(request('aktivitas') === 'approve_spp' ? 'selected' : ''); ?>>Approve SPP</option>
                        <option value="disburse" <?php echo e(request('aktivitas') === 'disburse' ? 'selected' : ''); ?>>Pencairan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo e(request('date_from')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo e(request('date_to')); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                    <a href="<?php echo e(route('admin.activity.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mt-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-bsmart">
        <div class="card-body p-0">
            <div class="table-responsive">
                <?php
                    $sort = request('sort', 'created_at');
                    $dir = request('direction', 'desc');
                ?>
                <table class="table align-middle text-12">
                    <thead class="bg-table-header">
                        <tr>
                            <th class="border-0 py-3 px-4">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sort === 'created_at' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'created_at' ? 'sort-active' : ''); ?>">
                                    Waktu
                                    <?php if($sort === 'created_at'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th class="border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'username', 'direction' => ($sort === 'username' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'username' ? 'sort-active' : ''); ?>">
                                    Username
                                    <?php if($sort === 'username'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th class="border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'role', 'direction' => ($sort === 'role' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'role' ? 'sort-active' : ''); ?>">
                                    Role
                                    <?php if($sort === 'role'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th class="border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'aktivitas', 'direction' => ($sort === 'aktivitas' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'aktivitas' ? 'sort-active' : ''); ?>">
                                    Aktivitas
                                    <?php if($sort === 'aktivitas'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th class="border-0 py-3">Deskripsi</th>
                            <th class="text-center border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'ip_address', 'direction' => ($sort === 'ip_address' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'ip_address' ? 'sort-active' : ''); ?>">
                                    IP
                                    <?php if($sort === 'ip_address'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 text-secondary"><?php echo e($a->created_at->format('d M Y H:i:s')); ?></td>
                            <td class="fw-bold"><?php echo e($a->username); ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo e($a->role); ?></span></td>
                            <td>
                                <span class="badge bg-<?php echo e($a->aktivitas === 'login' ? 'success' : ($a->aktivitas === 'logout' ? 'secondary' : 'primary')); ?>-subtle text-<?php echo e($a->aktivitas === 'login' ? 'success' : ($a->aktivitas === 'logout' ? 'secondary' : 'primary')); ?>">
                                    <?php echo e($a->aktivitas); ?>

                                </span>
                            </td>
                            <td class="text-muted"><?php echo e($a->deskripsi); ?></td>
                            <td class="text-center"><code class="small text-secondary"><?php echo e($a->ip_address); ?></code></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '6','icon' => 'fa-solid fa-clock','title' => 'Belum ada aktivitas tercatat.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '6','icon' => 'fa-solid fa-clock','title' => 'Belum ada aktivitas tercatat.']); ?>
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
            <div class="d-flex justify-content-center p-4"><?php echo e($activities->links()); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/activity/index.blade.php ENDPATH**/ ?>