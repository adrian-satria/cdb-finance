<?php $__env->startSection('title', 'Master Area | CDB Finance'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary m-0"><i class="fa-solid fa-location-dot me-2"></i>Master Data Area</h4>
        <a href="<?php echo e(route('admin.area.create')); ?>" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-plus me-1"></i> Tambah Area Baru
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle m-0 table-bsmart">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center border-0 py-3">No</th>
                            <th width="15%" class="text-center border-0 py-3">Kode Area</th>
                            <th width="40%" class="border-0 py-3">Nama Area</th>
                            <th width="30%" class="border-0 py-3">Project Terkait</th>
                            <th width="8%" class="text-center border-0 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr style="border-bottom: 1px solid #f1f3f4;">
                            <td class="text-center fw-semibold text-secondary py-3"><?php echo e($areas->firstItem() + $index); ?></td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    <?php echo e($a->kode_area); ?>

                                </span>
                            </td>
                            <td class="fw-semibold"><?php echo e($a->nama_area); ?></td>
                            <td>
                                <?php $__empty_2 = true; $__currentLoopData = $a->projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                    <span class="badge bg-info bg-opacity-10 text-info border me-1 mb-1">
                                        <?php echo e($p->kode_project); ?> - <?php echo e($p->nama_project); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                    <span class="text-muted small">Belum ada project</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?php echo e(route('admin.area.edit', $a->kode_area)); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <form action="<?php echo e(route('admin.area.destroy', $a->kode_area)); ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus area <?php echo e($a->kode_area); ?>?');" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Belum ada data area.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($areas->appends(request()->query())->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/area/index.blade.php ENDPATH**/ ?>