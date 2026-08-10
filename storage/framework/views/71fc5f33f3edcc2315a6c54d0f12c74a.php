<?php $__env->startSection('title', 'Pengaturan Sistem | B-SMART'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-gear text-secondary me-2"></i>Pengaturan Sistem</h4>
        <div class="d-flex gap-2">
            <form action="<?php echo e(route('admin.settings.create-default')); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> Default Settings
                </button>
            </form>
            <form action="<?php echo e(route('admin.settings.clear-cache')); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-warning btn-sm">
                    <i class="fa-solid fa-rotate me-1"></i> Clear Cache
                </button>
            </form>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle text-13">
                    <thead class="bg-table-header">
                        <tr>
                            <th class="border-0 py-3">Group</th>
                            <th class="border-0 py-3">Label</th>
                            <th class="border-0 py-3">Key</th>
                            <th class="border-0 py-3">Value</th>
                            <th class="text-center border-0 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $settings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border"><?php echo e($s->group); ?></span></td>
                            <td class="fw-semibold"><?php echo e($s->label); ?></td>
                            <td><code><?php echo e($s->key); ?></code></td>
                            <td>
                                <form action="<?php echo e(route('admin.settings.update')); ?>" method="POST" class="row g-1">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="key" value="<?php echo e($s->key); ?>">
                                    <div class="col-8">
                                        <?php if($s->type === 'boolean'): ?>
                                            <select name="value" class="form-select form-select-sm">
                                                <option value="true" <?php echo e($s->value === 'true' ? 'selected' : ''); ?>>Aktif</option>
                                                <option value="false" <?php echo e($s->value === 'false' ? 'selected' : ''); ?>>Nonaktif</option>
                                            </select>
                                        <?php else: ?>
                                            <input type="<?php echo e($s->type === 'number' ? 'number' : 'text'); ?>" name="value" value="<?php echo e($s->value); ?>" class="form-control form-control-sm">
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-4">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Simpan</button>
                                    </div>
                                </form>
                            </td>
                            <td class="text-center">
                                <span class="text-muted small"><?php echo e($s->type); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '5','title' => 'Belum Ada Pengaturan','message' => 'Klik &lt;strong&gt;Default Settings&lt;/strong&gt; untuk membuat pengaturan awal.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '5','title' => 'Belum Ada Pengaturan','message' => 'Klik &lt;strong&gt;Default Settings&lt;/strong&gt; untuk membuat pengaturan awal.']); ?>
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
            <div class="d-flex justify-content-center mt-4"><?php echo e($settings->links()); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/settings/index.blade.php ENDPATH**/ ?>