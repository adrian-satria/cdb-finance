<div class="table-responsive">
    <table class="table align-middle m-0 table-bsmart">
        <thead>
            <tr>
                <th width="4%" class="text-center border-0 py-3">No</th>
                <th width="8%" class="text-center border-0 py-3">Project</th>
                <th width="10%" class="text-center border-0 py-3">Kode Budget</th>
                <th width="33%" class="border-0 py-3">Nama Komponen Anggaran</th>
                <th width="13%" class="text-end border-0 py-3">Alokasi Pagu (Budget)</th>
                <th width="12%" class="text-end border-0 py-3">Terserap (Actual)</th>
                <th width="12%" class="text-end border-0 py-3">Sisa Saldo</th>
                <th width="8%" class="text-center border-0 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody id="budgetTableBody">
            <?php $__empty_1 = true; $__currentLoopData = $budgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $alokasi = floatval($b->alokasi_dana ?? 0);
                $terserap = floatval($b->terserap ?? 0);
                $sisa_saldo = $alokasi - $terserap;
            ?>
            <tr style="border-bottom: 1px solid #f1f3f4;" class="hover-actions">
                <td class="text-center fw-semibold text-secondary py-3"><?php echo e($budgets->firstItem() + $index); ?></td>
                <td class="text-center">
                    <span class="badge bg-light text-dark border badge-custom"><?php echo e($b->kode_project); ?></span>
                </td>
                <td class="text-center fw-bold text-primary"><code><?php echo e($b->kode_budget); ?></code></td>
                <td class="py-3 pr-3">
                    <div class="fw-semibold text-dark mb-0.5" style="line-height: 1.4;"><?php echo e($b->nama_budget); ?></div>
                </td>
                <td class="text-end fw-semibold text-dark">Rp <?php echo e(number_format($alokasi, 0, ',', '.')); ?></td>
                <td class="text-end fw-semibold text-secondary">Rp <?php echo e(number_format($terserap, 0, ',', '.')); ?></td>
                <td class="text-end fw-bold <?php echo e($sisa_saldo <= 0 ? 'text-danger' : ($sisa_saldo < 2000000 ? 'text-warning' : 'text-success')); ?>">
                    Rp <?php echo e(number_format($sisa_saldo, 0, ',', '.')); ?>

                </td>
                <td class="text-center">
                    <div class="d-inline-flex gap-1">
                        <a href="<?php echo e(route('admin.budget.area', $b->id_budget)); ?>" class="btn btn-sm btn-outline-info btn-icon-circle" title="Atur per Area"><i class="fa-solid fa-location-dot"></i></a>
                        <a href="<?php echo e(route('admin.budget.edit', $b->id_budget)); ?>" class="btn btn-sm btn-outline-warning btn-icon-circle" title="Edit Budget"><i class="fa-regular fa-pen-to-square"></i></a>
                        <form action="<?php echo e(route('admin.budget.destroy', $b->id_budget)); ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus budget <?php echo e($b->kode_budget); ?> ?');" class="d-inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-circle" title="Hapus Budget"><i class="fa-regular fa-trash-can"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if($kodeProject): ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">Tidak ada data budget untuk project yang dipilih.</td></tr>
            <?php else: ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">Silakan pilih project terlebih dahulu.</td></tr>
            <?php endif; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if(count($budgets) > 0): ?>
<div class="d-flex justify-content-center mt-4">
    <?php echo e($budgets->appends(request()->query())->links()); ?>

</div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/budget/_table.blade.php ENDPATH**/ ?>