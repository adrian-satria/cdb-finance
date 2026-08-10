<?php $__env->startSection('title', isset($area) ? 'Edit Area' : 'Tambah Area'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <h4 class="fw-bold text-primary mb-4">
        <i class="fa-solid fa-location-dot me-2"></i><?php echo e(isset($area) ? 'Edit Area' : 'Tambah Area Baru'); ?>

    </h4>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <form method="POST" action="<?php echo e(isset($area) ? route('admin.area.update', $area->kode_area) : route('admin.area.store')); ?>">
                <?php echo csrf_field(); ?>
                <?php if(isset($area)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Kode Area <span class="text-danger">*</span></label>
                    <input type="text" name="kode_area" class="form-control <?php $__errorArgs = ['kode_area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e(old('kode_area', $area->kode_area ?? '')); ?>"
                        <?php echo e(isset($area) ? 'readonly' : ''); ?> maxlength="20">
                    <?php $__errorArgs = ['kode_area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Area <span class="text-danger">*</span></label>
                    <input type="text" name="nama_area" class="form-control <?php $__errorArgs = ['nama_area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e(old('nama_area', $area->nama_area ?? '')); ?>" maxlength="100">
                    <?php $__errorArgs = ['nama_area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-regular fa-floppy-disk me-1"></i> Simpan
                    </button>
                    <a href="<?php echo e(route('admin.area.index')); ?>" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/area/form.blade.php ENDPATH**/ ?>