<?php $__env->startSection('title', 'Audit Trail System Log | B-SMART'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-user-shield text-danger me-2"></i>Sistem Audit Trail</h4>
        <p class="text-muted small m-0 mt-1">Rekam jejak aktivitas digital user, manipulasi data transaksi keuangan, dan log otorisasi sistem B-SMART.</p>
    </div>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                <?php
                    $sort = request('sort', 'created_at');
                    $dir = request('direction', 'desc');
                ?>
                <table class="table align-middle border-light-table">
                    <thead class="bg-table-header text-13">
                        <tr>
                            <th width="5%" class="text-center border-0 py-3">No</th>
                            <th width="15%" class="border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sort === 'created_at' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'created_at' ? 'sort-active' : ''); ?>">
                                    Waktu Kejadian
                                    <?php if($sort === 'created_at'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th width="12%" class="border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'username', 'direction' => ($sort === 'username' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'username' ? 'sort-active' : ''); ?>">
                                    Username
                                    <?php if($sort === 'username'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th width="10%" class="border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'role', 'direction' => ($sort === 'role' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'role' ? 'sort-active' : ''); ?>">
                                    Role Akses
                                    <?php if($sort === 'role'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th width="15%" class="border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'aksi', 'direction' => ($sort === 'aksi' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'aksi' ? 'sort-active' : ''); ?>">
                                    Kategori Aksi
                                    <?php if($sort === 'aksi'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th width="33%" class="border-0 py-3">Deskripsi Kronologi</th>
                            <th width="10%" class="text-center border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'ip_address', 'direction' => ($sort === 'ip_address' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'ip_address' ? 'sort-active' : ''); ?>">
                                    IP Address
                                    <?php if($sort === 'ip_address'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-13">
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr style="border-bottom: 1px solid #f1f3f4;">
                            <td class="text-center fw-semibold text-secondary py-3"><?php echo e($logs->firstItem() + $index); ?></td>
                            <td class="text-secondary"><?php echo e(date('d M Y | H:i:s', strtotime($log->created_at))); ?> WIB</td>
                            <td><span class="fw-bold text-dark"><?php echo e($log->username); ?></span></td>
                            <td>
                                <?php if($log->role == 'ADMIN'): ?>
                                    <span class="badge bg-danger-subtle text-danger rounded px-2 py-1" style="font-size: 10px;">ADMIN</span>
                                <?php elseif($log->role == 'CHECKER'): ?>
                                    <span class="badge bg-success-subtle text-success rounded px-2 py-1" style="font-size: 10px;">CHECKER</span>
                                <?php else: ?>
                                    <span class="badge bg-primary-subtle text-primary rounded px-2 py-1" style="font-size: 10px;">MAKER</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(str_contains($log->aksi, 'INSERT') || str_contains($log->aksi, 'TAMBAH')): ?>
                                    <span class="text-primary fw-bold"><i class="fa-solid fa-square-plus me-1"></i> <?php echo e($log->aksi); ?></span>
                                <?php elseif(str_contains($log->aksi, 'APPROVAL')): ?>
                                    <span class="text-success fw-bold"><i class="fa-solid fa-shield-check me-1"></i> <?php echo e($log->aksi); ?></span>
                                <?php else: ?>
                                    <span class="text-secondary fw-bold"><i class="fa-solid fa-circle-dot me-1"></i> <?php echo e($log->aksi); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-dark fw-normal"><?php echo e($log->deskripsi); ?></td>
                            <td class="text-center text-muted"><code class="small text-secondary"><?php echo e($log->ip_address); ?></code></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '7','icon' => 'fa-solid fa-clock-rotate-left','title' => 'Belum ada rekaman log aktivitas sistem.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '7','icon' => 'fa-solid fa-clock-rotate-left','title' => 'Belum ada rekaman log aktivitas sistem.']); ?>
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
            
            <!-- Pagination Links -->
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($logs->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/audit_trail/index.blade.php ENDPATH**/ ?>