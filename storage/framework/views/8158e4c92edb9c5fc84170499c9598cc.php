<?php $__env->startSection('title', 'Edit Akses User'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card card-bsmart">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold text-primary m-0"><i class="fa-solid fa-user-shield me-2"></i>Edit Akses User</h5>
                    <div class="text-muted" style="font-size:13px;"><?php echo e($user->nama); ?> (<?php echo e($user->username); ?>)</div>
                </div>

                <div class="card-body p-4">
                    <?php
                        $roles = [
                            'ADMIN','MAKER','CHECKER','KASIR_PUSAT','DIREKTUR','MANAGER_KEUANGAN',
                            'AREA_MANAGER','FINANCE_PROJECT','PROJECT_MANAGER',
                            'KOORDINATOR_KEUANGAN','KOORDINATOR_PK','KOORDINATOR_TC',
                            'KOORDINATOR_DIKLAT','KOORDINATOR_KLINIK','KOORDINATOR_BATRA',
                            'MANAGER_PKP'
                        ];
                    ?>

                    <form action="<?php echo e(route('admin.user.access.update', $user->id_user)); ?>" method="POST" data-loading>
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px; border-radius:8px;">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                <b>Gagal menyimpan:</b>
                                <ul class="mb-0 mt-1">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if(session('success')): ?>
                            <div class="alert alert-success py-2 px-3 mb-3" style="font-size:13px; border-radius:8px;">
                                <i class="fa-solid fa-circle-check me-1"></i> <?php echo e(session('success')); ?>

                            </div>
                        <?php endif; ?>

                        <div class="mb-3 text-muted" style="font-size:13px;">
                            <strong>Catatan:</strong>
                            <ul class="mb-0 ps-3" style="font-size:13px;">
                                <li>Klik <strong>Tambah Role</strong> untuk menambah baris role baru.</li>
                                <li>Klik ikon <i class="fa-solid fa-trash text-danger"></i> untuk menghapus baris.</li>
                                <li>Role <code>MAKER</code> / <code>AREA_MANAGER</code> — area-scoped.</li>
                                <li>Role <code>FINANCE_PROJECT</code> / <code>PROJECT_MANAGER</code> / <code>MANAGER_KEUANGAN</code> — project-scoped.</li>
                            </ul>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle" id="aksesTable" style="border: 1px solid rgba(0,0,0,.06); border-radius: 10px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:5%" class="text-center">Aksi</th>
                                        <th>Role</th>
                                        <th>Jabatan (label)</th>
                                        <th>Area</th>
                                        <th style="width:25%">Kode Project (opsional)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $akses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusRole(this)">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <select name="akses[<?php echo e($i); ?>][role]" class="form-select form-select-sm <?php $__errorArgs = ["akses.$i.role"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($r); ?>" <?php echo e(old("akses.$i.role", $a->role ?? '') === $r ? 'selected' : ''); ?>><?php echo e($r); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <?php $__errorArgs = ["akses.$i.role"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </td>
                                            <td>
                                                <input type="text" name="akses[<?php echo e($i); ?>][jabatan]" class="form-control form-control-sm <?php $__errorArgs = ["akses.$i.jabatan"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required value="<?php echo e(old("akses.$i.jabatan", $a->jabatan ?? '')); ?>" placeholder="Label jabatan">
                                                <?php $__errorArgs = ["akses.$i.jabatan"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </td>
                                            <td>
                                                <select name="akses[<?php echo e($i); ?>][kode_area]" class="form-select form-select-sm <?php $__errorArgs = ["akses.$i.kode_area"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                                    <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($ar->kode_area); ?>" <?php echo e(old("akses.$i.kode_area", $a->kode_area ?? '') === $ar->kode_area ? 'selected' : ''); ?>>
                                                            <?php echo e($ar->nama_area); ?> (<?php echo e($ar->kode_area); ?>)
                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <?php $__errorArgs = ["akses.$i.kode_area"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </td>
                                            <td>
                                                <?php $selectedCsv = old("akses.$i.kode_project", $a->kode_project); ?>
                                                <?php echo $__env->make('admin.user._project_multiselect', [
                                                    'name' => "akses[$i][kode_project]",
                                                    'projects' => $projects,
                                                    'selectedCsv' => $selectedCsv
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '5','icon' => 'fa-solid fa-user-slash','title' => 'User belum punya akses.','message' => 'Klik &lt;strong&gt;Tambah Role&lt;/strong&gt; di bawah.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '5','icon' => 'fa-solid fa-user-slash','title' => 'User belum punya akses.','message' => 'Klik &lt;strong&gt;Tambah Role&lt;/strong&gt; di bawah.']); ?>
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

                        <div class="d-flex gap-2 mb-4">
                            <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="tambahRole()">
                                <i class="fa-solid fa-plus me-1"></i> Tambah Role
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="/admin/user" class="btn btn-light px-4 fw-semibold text-secondary">Batal</a>

                            <div class="d-flex gap-2">
                                <form action="<?php echo e(route('admin.user.access.clear', $user->id_user)); ?>" method="POST" onsubmit="return confirm('Hapus semua akses user ini?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-outline-danger fw-semibold">Hapus Semua Akses</button>
                                </form>

                                <button type="submit" class="btn btn-primary px-4 fw-semibold">Simpan Akses</button>
                            </div>
                        </div>
                    </form>

                    <template id="role-row-template">
                        <tr>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusRole(this)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                            <td>
                                <select name="akses[IDX][role]" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Role --</option>
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($r); ?>"><?php echo e($r); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="akses[IDX][jabatan]" class="form-control form-control-sm" required placeholder="Label jabatan">
                            </td>
                            <td>
                                <select name="akses[IDX][kode_area]" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Area --</option>
                                    <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($ar->kode_area); ?>"><?php echo e($ar->nama_area); ?> (<?php echo e($ar->kode_area); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </td>
                            <td>
                                <?php echo $__env->make('admin.user._project_multiselect', [
                                    'name' => "akses[IDX][kode_project]",
                                    'projects' => $projects,
                                    'selectedCsv' => ''
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </td>
                        </tr>
                    </template>

                    <script>
                        let roleIdx = <?php echo e(max($akses->count(), 0)); ?>;

                        function tambahRole() {
                            const tbody = document.querySelector('#aksesTable tbody');
                            const template = document.getElementById('role-row-template');
                            const html = template.innerHTML.replace(/IDX/g, roleIdx++);
                            tbody.insertAdjacentHTML('beforeend', html);
                        }

                        function hapusRole(btn) {
                            const tbody = document.querySelector('#aksesTable tbody');
                            if (tbody.children.length <= 1) {
                                alert('Minimal harus ada 1 role.');
                                return;
                            }
                            btn.closest('tr').remove();
                        }
                    </script>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/user/access_edit.blade.php ENDPATH**/ ?>