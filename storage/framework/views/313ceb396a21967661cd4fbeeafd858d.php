<?php $__env->startSection('title', isset($project) ? 'Edit Project' : 'Tambah Project'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <h4 class="fw-bold text-primary mb-4">
        <i class="fa-solid fa-diagram-project me-2"></i><?php echo e(isset($project) ? 'Edit Project' : 'Tambah Project Baru'); ?>

    </h4>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <form method="POST" action="<?php echo e(isset($project) ? route('admin.project.update', $project->kode_project) : route('admin.project.store')); ?>">
                <?php echo csrf_field(); ?>
                <?php if(isset($project)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Kode Project <span class="text-danger">*</span></label>
                    <input type="text" name="kode_project" class="form-control <?php $__errorArgs = ['kode_project'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e(old('kode_project', $project->kode_project ?? '')); ?>"
                        <?php echo e(isset($project) ? 'readonly' : ''); ?> maxlength="10">
                    <?php $__errorArgs = ['kode_project'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Project <span class="text-danger">*</span></label>
                    <input type="text" name="nama_project" class="form-control <?php $__errorArgs = ['nama_project'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e(old('nama_project', $project->nama_project ?? '')); ?>" maxlength="100">
                    <?php $__errorArgs = ['nama_project'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Area Kerja</label>
                    <small class="text-muted d-block mb-2">Pilih area yang terkait dengan project ini.</small>
                    <div class="row">
                        <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-4 col-lg-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="areas[]"
                                    value="<?php echo e($a->kode_area); ?>" id="area_<?php echo e($a->kode_area); ?>"
                                    <?php echo e(in_array($a->kode_area, old('areas', isset($project) ? $project->areas->pluck('kode_area')->toArray() : [])) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="area_<?php echo e($a->kode_area); ?>">
                                    <strong><?php echo e($a->kode_area); ?></strong> - <?php echo e($a->nama_area); ?>

                                </label>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php $__errorArgs = ['areas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-regular fa-floppy-disk me-1"></i> Simpan
                    </button>
                    <a href="<?php echo e(route('admin.project.index')); ?>" class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/project/form.blade.php ENDPATH**/ ?>