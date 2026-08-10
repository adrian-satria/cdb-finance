<?php $__env->startSection('title', 'Riwayat Pengajuan SPP | B-SMART'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <?php if(session('success')): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4 p-3 rounded-4 d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-check fs-4 me-3 text-success"></i>
            <div class="fw-semibold text-dark"><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4 p-3 rounded-4 d-flex align-items-center" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-4 me-3 text-danger"></i>
            <div class="fw-semibold text-dark"><?php echo e(session('error')); ?></div>
        </div>
    <?php endif; ?>

    <div class="card card-bsmart">
        <div class="card-header bg-transparent border-0 d-flex flex-wrap justify-content-between align-items-center px-4 pt-4 pb-0">
            <div>
                <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-table-list text-primary me-2"></i>Data SPP</h4>
                <p class="text-muted small m-0 mt-1">Riwayat pengajuan Surat Permintaan Pembayaran.</p>
            </div>
            <a href="/spp/tambah" class="btn btn-primary btn-sm px-3 mt-2 mt-md-0">
                <i class="fa-solid fa-plus me-1"></i> Input SPP Baru
            </a>
        </div>

        <div class="card-body p-4">
            <?php if(isset($projects)): ?>
            <div class="row align-items-center mb-4">
                <div class="col-12">
                    <form action="/spp" method="GET" class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label for="kode_project" class="form-label small text-muted mb-1">Filter Project</label>
                            <div class="d-flex gap-2">
                                <select name="kode_project" id="kode_project" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto; min-width: 200px;">
                                    <option value="">Semua Project</option>
                                    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($project->kode_project); ?>" <?php echo e(($selectedProject == $project->kode_project) ? 'selected' : ''); ?>>
                                            <?php echo e($project->kode_project); ?> - <?php echo e($project->nama_project ?? $project->kode_project); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="submit" class="btn btn-outline-secondary btn-sm">Terapkan</button>
                                <?php if(request('kode_project')): ?>
                                    <a href="/spp" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-xmark me-1"></i>Reset</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php
                $sort = request('sort', 'created_at');
                $dir = request('direction', 'desc');
            ?>
            <div class="table-responsive">
                <table class="table align-middle table-bsmart">
                    <thead>
                        <tr>
                            <th width="4%" class="text-center border-0 py-3">No</th>
                            <th width="14%" class="border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'no_surat', 'direction' => ($sort === 'no_surat' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'no_surat' ? 'sort-active' : ''); ?>">
                                    No. Surat
                                    <?php if($sort === 'no_surat'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th width="10%" class="border-0 py-3" title="Kode program / project">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'kode_project', 'direction' => ($sort === 'kode_project' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'kode_project' ? 'sort-active' : ''); ?>">
                                    Project
                                    <?php if($sort === 'kode_project'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th width="11%" class="border-0 py-3">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'tanggal', 'direction' => ($sort === 'tanggal' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'tanggal' ? 'sort-active' : ''); ?>">
                                    Tanggal
                                    <?php if($sort === 'tanggal'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th width="10%" class="border-0 py-3" title="Unit kerja pengaju">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'kode_area', 'direction' => ($sort === 'kode_area' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'kode_area' ? 'sort-active' : ''); ?>">
                                    Unit / Area
                                    <?php if($sort === 'kode_area'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th width="18%" class="border-0 py-3" title="Dokumen lampiran pendukung">Lampiran Berkas</th>
                            <th width="14%" class="border-0 py-3" title="Total dana yang diajukan">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'total_nominal', 'direction' => ($sort === 'total_nominal' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'total_nominal' ? 'sort-active' : ''); ?>">
                                    Total Nominal
                                    <?php if($sort === 'total_nominal'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th width="11%" class="text-center border-0 py-3" title="Tahap persetujuan saat ini">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'status_surat', 'direction' => ($sort === 'status_surat' && $dir === 'asc') ? 'desc' : 'asc'])); ?>" class="sortable <?php echo e($sort === 'status_surat' ? 'sort-active' : ''); ?>">
                                    Status
                                    <?php if($sort === 'status_surat'): ?><span class="sort-indicator"><?php echo $dir === 'asc' ? '&#9650;' : '&#9660;'; ?></span><?php endif; ?>
                                </a>
                            </th>
                            <th width="13%" class="text-center border-0 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr style="border-bottom: 1px solid #f1f3f4;">
                            <td class="text-center fw-semibold text-secondary py-3"><?php echo e($data->firstItem() + $index); ?></td>              
                            
                            <td>
                                <a href="javascript:void(0)" class="fw-bold text-dark text-decoration-none hover-primary" data-action="detail-spp" data-no-surat="<?php echo e($s->no_surat); ?>" title="Klik untuk lihat rincian anggaran">
                                    <?php echo e($s->no_surat); ?> <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-muted" style="font-size: 10px;"></i>
                                </a>
                            </td>                         
                            
                            <td class="text-secondary"><span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1.5" style="border-radius: 6px;"><?php echo e($s->kode_project); ?></span></td>
                            <td class="text-secondary"><?php echo e(date('d M Y', strtotime($s->tanggal))); ?></td>                             
                            
                            <td><span class="badge bg-light text-dark border px-2.5 py-1.5" style="border-radius: 6px;"><?php echo e($s->kode_area); ?></span></td>                             
                            
                            <td>
                                <?php if(isset($s->files_maker) && $s->files_maker->count() > 0): ?>
                                    <div class="mb-2">
                                        <span class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;">STAF (MAKER):</span>
                                        <?php $__currentLoopData = $s->files_maker; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="/spp/file/<?php echo e($fm->nama_file); ?>" target="_blank" class="d-inline-flex align-items-center text-decoration-none text-primary me-1 mb-1 bg-light border px-2 py-1 rounded" style="font-size: 11px; font-weight: 500;" title="Buka lampiran staf">
                                                <i class="fa-solid fa-paperclip me-1 text-secondary"></i> Doc-<?php echo e($loop->iteration); ?>

                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if(isset($s->files_checker) && $s->files_checker->count() > 0): ?>
                                    <div>
                                        <span class="text-success d-block mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;">MANAGEMENT (CHECKER):</span>
                                        <?php $__currentLoopData = $s->files_checker; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="/spp/file/<?php echo e($fc->nama_file); ?>" target="_blank" class="d-inline-flex align-items-center text-decoration-none text-success me-1 mb-1 bg-success-subtle border border-success-subtle px-2 py-1 rounded" style="font-size: 11px; font-weight: 500;" title="Buka berkas otorisasi">
                                                <i class="fa-solid fa-file-shield me-1 text-success"></i> Bukti-<?php echo e($loop->iteration); ?>

                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if((!isset($s->files_maker) || $s->files_maker->count() == 0) && (!isset($s->files_checker) || $s->files_checker->count() == 0)): ?>
                                    <span class="text-muted small px-2 py-1 bg-light rounded text-center d-inline-block" style="font-size: 11px; font-style: italic;">Tidak ada berkas</span>
                                <?php endif; ?>
                            </td>                             
                            
                            <td class="fw-bold text-dark">Rp <?php echo e(number_format($s->total_nominal, 0, ',', '.')); ?></td>
                            
                            <td class="text-center">
                                <?php if($s->status_surat == 'Pending' || $s->status_surat == 'Pending Director Otorisasi'): ?>
                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1.5" style="font-size: 11px;"><?php echo e($s->status_surat); ?></span>
                                <?php elseif($s->status_surat == 'Approved'): ?>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1.5" style="font-size: 11px;">Approved</span>
                                <?php elseif($s->status_surat == 'Disbursed'): ?>
                                    <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1.5" style="font-size: 11px; background-color: #e0f2fe !important; color: #0369a1 !important;">Disbursed</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5" style="font-size: 11px;">Rejected</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="text-center">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    
                                    <?php if(session('role') == $s->posisi_saat_ini && str_contains($s->status_surat, 'Pending')): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary px-3 rounded-pill" style="font-size: 11.5px; font-weight: 500;" data-action="validasi-spp" data-no-surat="<?php echo e($s->no_surat); ?>" data-total-nominal="<?php echo e($s->total_nominal); ?>">
                                            <i class="fa-solid fa-user-check me-1"></i> Periksa
                                        </button>
                                    
                                    <?php elseif(session('role') == 'KASIR_PUSAT' && $s->status_surat == 'Approved' && $s->posisi_saat_ini == 'KASIR_PUSAT'): ?>
                                        <button type="button" class="btn btn-sm btn-success px-3 rounded-pill" style="font-size: 11.5px; font-weight: 600;" data-action="cairkan-spp" data-no-surat="<?php echo e($s->no_surat); ?>" data-total-nominal="<?php echo e($s->total_nominal); ?>">
                                            <i class="fa-solid fa-money-bill-transfer me-1"></i> Cairkan Dana
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small" style="font-style: italic;">
                                            <?php if($s->status_surat == 'Disbursed'): ?>
                                                <span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Selesai Cair</span>
                                            <?php else: ?>
                                                <code>Otoritas: <?php echo e($s->posisi_saat_ini); ?></code>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if($s->status_surat == 'Approved' || $s->status_surat == 'Disbursed'): ?>
                                        <?php $canPrint = in_array(session('role'), ['ADMIN', 'MANAGER_KEUANGAN'], true) || session('role') === 'DIREKTUR'; ?>
                                        <?php if($canPrint): ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-icon-circle" title="Preview Hasil Cetak SPP" data-action="preview-spp" data-no-surat="<?php echo e($s->no_surat); ?>">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <a href="/spp/cetak?no_surat=<?php echo e(rawurlencode($s->no_surat)); ?>" target="_blank" class="btn btn-sm btn-outline-secondary btn-icon-circle" title="Cetak Bukti Dokumen PDF SPP">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '9','title' => 'Belum Ada Data','message' => 'Belum ada pengajuan SPP yang tersedia.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '9','title' => 'Belum Ada Data','message' => 'Belum ada pengajuan SPP yang tersedia.']); ?>
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
                <?php echo e($data->appends(request()->query())->links()); ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('modals'); ?>
<?php echo $__env->make('spp.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Functions provided by resources/js/modules/spp.js (exposed on window)
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/spp/index.blade.php ENDPATH**/ ?>