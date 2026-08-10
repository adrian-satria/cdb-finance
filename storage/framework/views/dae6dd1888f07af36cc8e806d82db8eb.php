<?php $__env->startSection('title', 'Master Budget | CDB Finance'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary m-0"><i class="fa-solid fa-folder-tree me-2"></i>Master Data Kode Budget</h4>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.budget.import.form')); ?>" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                <i class="fa-solid fa-file-import me-1"></i> Import CSV
            </a>
            <a href="<?php echo e(route('admin.budget.create')); ?>" class="btn btn-primary btn-sm px-3">
                <i class="fa-solid fa-plus me-1"></i> Tambah Budget Baru
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Search / Filter -->
    <div class="card card-bsmart mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end" id="filterForm">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Project</label>
                    <select name="kode_project" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Pilih Project --</option>
                        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->kode_project); ?>" <?php echo e($kodeProject == $p->kode_project ? 'selected' : ''); ?>>
                                <?php echo e($p->kode_project); ?> - <?php echo e($p->nama_project); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Cari Budget</label>
                    <input type="text" name="search" id="searchInput" class="form-control form-control-sm" value="<?php echo e(request('search')); ?>" placeholder="Ketik untuk mencari..." oninput="debounceSearch(this)">
                </div>
                <div class="col-md-2 d-flex gap-1 align-items-end">
                    <?php if($kodeProject || request('search')): ?>
                    <a href="<?php echo e(route('admin.budget.index')); ?>" class="btn btn-outline-danger btn-sm px-3"><i class="fa-solid fa-xmark me-1"></i>Reset</a>
                    <?php endif; ?>
                </div>
            </form>
            <script>
                let searchTimer;
                function loadBudgetTable() {
                    var project = document.querySelector('[name="kode_project"]').value;
                    var search = document.getElementById('searchInput').value;
                    var params = new URLSearchParams();
                    if (project) params.set('kode_project', project);
                    if (search) params.set('search', search);

                    fetch('<?php echo e(route("admin.budget.index")); ?>?' + params.toString(), {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        document.getElementById('budgetTableContainer').innerHTML = data.html;
                    });
                }

                function debounceSearch(input) {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() {
                        if (input.value.length >= 2 || input.value === '') {
                            loadBudgetTable();
                        }
                    }, 400);
                }

                document.querySelector('[name="kode_project"]').addEventListener('change', function() {
                    loadBudgetTable();
                });

                <?php if(request('search')): ?>
                document.addEventListener('DOMContentLoaded', function() {
                    var el = document.getElementById('searchInput');
                    if (el) { el.focus(); el.selectionStart = el.selectionEnd = el.value.length; }
                });
                <?php endif; ?>
            </script>
        </div>
    </div>

    <div class="card card-bsmart">
        <div class="card-body p-4" id="budgetTableContainer">
            <?php echo $__env->make('admin.budget._table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cdb-finance\resources\views/admin/budget/index.blade.php ENDPATH**/ ?>