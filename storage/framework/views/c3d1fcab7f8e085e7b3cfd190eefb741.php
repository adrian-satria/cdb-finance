<?php $__env->startSection('title', 'Kelola User | B-SMART'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary m-0"><i class="fa-solid fa-users-gear me-2"></i>Manajemen Data Pengguna</h4>
        <a href="/admin/user/create" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah User Baru
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    
    <div class="card card-bsmart mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Cari User</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="<?php echo e(request('search')); ?>" placeholder="Nama, username...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Cari</button>
                </div>
                <?php if(request('search')): ?>
                <div class="col-auto">
                    <a href="<?php echo e(route('admin.user.index')); ?>" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-xmark me-1"></i>Reset</a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle border-light table-bsmart">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center border-0 py-3">No</th>
                            <th width="45%" class="border-0 py-3">Nama Lengkap</th>
                            <th width="20%" class="border-0 py-3">Username</th>
                            <th width="35%" class="border-0 py-3">Jabatan / Akses</th>
                            <th width="15%" class="text-center border-0 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center fw-semibold text-secondary py-3"><?php echo e($users->firstItem() + $index); ?></td>
                            <td class="fw-bold text-dark py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size:12px;">
                                        <?php echo e(strtoupper(substr($u->nama, 0, 2))); ?>

                                    </div>
                                    <?php echo e($u->nama); ?>

                                </div>
                            </td>
                            <td><span class="badge bg-light text-secondary border badge-custom"><?php echo e($u->username); ?></span></td>
                            <td>
                                <?php
                                    $akses = $u->akses ?? collect();
                                    $items = $akses->map(function($a){
                                        $jabatan = $a->jabatan ?? '-';
                                        $kodeArea = $a->kode_area ?? '-';
                                        $role = $a->role ?? '-';
                                        return "{$jabatan} ({$role}) - {$kodeArea}";
                                    })->filter();
                                ?>
                                <?php if($items->count() > 0): ?>
                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="small text-dark" style="line-height:1.35;">• <?php echo e($line); ?></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <span class="text-muted small">Belum diatur</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                               <div class="d-flex justify-content-center gap-2">
                                    <?php $userId = $u->id_user ?? $u->id; ?>
                                    <a href="<?php echo e(route('admin.user.edit', $userId)); ?>" class="btn btn-sm btn-outline-warning btn-icon-circle" title="Edit User">
                                        <i class="fa-solid fa-user-pen"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.user.access.edit', $userId)); ?>" class="btn btn-sm btn-outline-info btn-icon-circle" title="Edit Akses">
                                        <i class="fa-solid fa-user-shield"></i>
                                    </a>

                                    <form action="<?php echo e(route('admin.user.destroy', $userId)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini dari sistem?')" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-circle" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '5','title' => 'Belum Ada User','message' => 'Belum ada pengguna yang terdaftar di sistem.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '5','title' => 'Belum Ada User','message' => 'Belum ada pengguna yang terdaftar di sistem.']); ?>
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
            
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($users->appends(request()->query())->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/user/index.blade.php ENDPATH**/ ?>