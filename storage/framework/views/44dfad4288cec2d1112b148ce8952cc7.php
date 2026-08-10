<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'CDB Finance - B-SMART'); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>

<div class="app-wrapper">
    <div class="sidebar" id="sidebarMenu">
        <a href="/spp" class="sidebar-brand">
            <div class="sidebar-logo">CDB</div>
            <div class="sidebar-title-wrap">
                <span class="sidebar-app-name">Finance Management System</span>
            </div>
        </a>
        
        <div class="sidebar-nav">
            <?php if(session('role') != 'ADMIN'): ?>
            <div class="nav-section-label">Main System</div>

            <a href="/dashboard" class="sidebar-item <?php echo e(request()->is('dashboard') ? 'active' : ''); ?>">
                <div class="d-flex align-items-center">
                    <div class="nav-icon"><i class="fa-solid fa-gauge-high"></i></div>
                    <span class="nav-label">Dashboard</span>
                </div>
            </a>
            
            <a href="#sppSubMenu" data-bs-toggle="collapse" aria-expanded="<?php echo e(request()->is('spp*') ? 'true' : 'false'); ?>" class="sidebar-item <?php echo e(request()->is('spp*') ? 'active' : ''); ?>">
                <div class="d-flex align-items-center">
                    <div class="nav-icon"><i class="fa-solid fa-wallet"></i></div>
                    <span class="nav-label">Transaksi SPP</span>
                </div>
                <i class="fa-solid fa-chevron-down toggle-icon fs-7"></i>
            </a>
            
            <div class="collapse sidebar-child-menu <?php echo e(request()->is('spp*') ? 'show' : ''); ?>" id="sppSubMenu">
                <a href="/spp" class="<?php echo e(request()->is('spp') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-table-list me-2"></i>Data SPP
                </a>

                <a href="/spp/tambah" class="<?php echo e(request()->is('spp/tambah') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-file-circle-plus me-2"></i>Input SPP Baru
                </a>


                <?php if(session('role') == 'MANAGER_KEUANGAN'): ?>
                <a href="/spp/kelola" class="<?php echo e(request()->is('spp/kelola') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-clipboard-check me-2"></i>Kelola Surat
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if(session('role') == 'ADMIN'): ?>
            <div class="nav-section-label mt-3">Fitur Admin</div>

            <a href="/dashboard" class="sidebar-item <?php echo e(request()->is('dashboard') ? 'active' : ''); ?>">
                <div class="d-flex align-items-center">
                    <div class="nav-icon"><i class="fa-solid fa-gauge-high"></i></div>
                    <span class="nav-label">Dashboard</span>
                </div>
            </a>

            <a href="#adminSubMenu" data-bs-toggle="collapse" aria-expanded="<?php echo e(request()->is('admin*') ? 'true' : 'false'); ?>" class="sidebar-item <?php echo e(request()->is('admin*') ? 'active' : ''); ?>">
                <div class="d-flex align-items-center">
                    <div class="nav-icon"><i class="fa-solid fa-sliders"></i></div>
                    <span class="nav-label">Data Master</span>
                </div>
                <i class="fa-solid fa-chevron-down toggle-icon fs-7"></i>
            </a>

            <div class="collapse sidebar-child-menu <?php echo e(request()->is('admin*') ? 'show' : ''); ?>" id="adminSubMenu">
                <a href="/admin/project" class="<?php echo e(request()->is('admin/project*') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-diagram-project me-2"></i>Master Project
                </a>
                <a href="/admin/area" class="<?php echo e(request()->is('admin/area*') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-location-dot me-2"></i>Master Area
                </a>
                <a href="/admin/budget" class="<?php echo e(request()->is('admin/budget*') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-folder-tree me-2"></i>Master Budget
                </a>
                <a href="/admin/user" class="<?php echo e(request()->is('admin/user*') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-users-gear me-2"></i>Kelola User
                </a>
            </div>

            <a href="/admin/audit-trail" class="sidebar-item <?php echo e(Request::is('admin/audit-trail') ? 'active' : ''); ?>">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-user-secret nav-icon me-3"></i>
                    <span class="nav-label">Audit Trail Log</span>
                </div>
            </a>

            <a href="#reportsSubMenu" data-bs-toggle="collapse" aria-expanded="<?php echo e(request()->is('admin/reports*') ? 'true' : 'false'); ?>" class="sidebar-item <?php echo e(request()->is('admin/reports*') ? 'active' : ''); ?>">
                <div class="d-flex align-items-center">
                    <div class="nav-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <span class="nav-label">Laporan</span>
                </div>
                <i class="fa-solid fa-chevron-down toggle-icon fs-7"></i>
            </a>

            <div class="collapse sidebar-child-menu <?php echo e(request()->is('admin/reports*') ? 'show' : ''); ?>" id="reportsSubMenu">
                <a href="<?php echo e(route('admin.reports.budget_vs_actual')); ?>" class="<?php echo e(request()->is('admin/reports/budget-vs-actual') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-chart-bar me-2"></i>Budget vs Actual
                </a>
                <a href="<?php echo e(route('admin.reports.financial_summary')); ?>" class="<?php echo e(request()->is('admin/reports/financial-summary') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-file-invoice me-2"></i>Ringkasan Keuangan
                </a>
                <a href="<?php echo e(route('admin.reports.area_performance')); ?>" class="<?php echo e(request()->is('admin/reports/area-performance') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-map-location-dot me-2"></i>Performa Area
                </a>
            </div>

            <a href="<?php echo e(route('admin.activity.index')); ?>" class="sidebar-item <?php echo e(Request::is('admin/activity') ? 'active' : ''); ?>">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-activity nav-icon me-3"></i>
                    <span class="nav-label">Monitor Aktivitas</span>
                </div>
            </a>

            <a href="<?php echo e(route('admin.settings.index')); ?>" class="sidebar-item <?php echo e(Request::is('admin/settings') ? 'active' : ''); ?>">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-gear nav-icon me-3"></i>
                    <span class="nav-label">Pengaturan Sistem</span>
                </div>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="sidebar-footer"></div>
    </div>

    <form id="logout-form" action="/logout" method="POST" class="d-none"><?php echo csrf_field(); ?></form>

    <div class="main-content" id="mainContent">
        <div class="topbar">
            <button type="button" class="topbar-toggle" id="toggleSidebarBtn"><i class="fa-solid fa-bars"></i></button>
            <div class="topbar-title ms-2">Unit Kerja: <span class="badge-primary-custom"><?php echo e(session('kode_area') ?? 'PUSAT'); ?></span></div>

            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-light position-relative pill" type="button" data-bs-toggle="dropdown" style="padding: 8px 12px;">
                        <i class="fa-solid fa-bell text-secondary"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-9" id="notifBadge" style="display: none;">
                            0
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 radius-md" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold" style="font-size: 13px;">Notifikasi</h6>
                            <a href="<?php echo e(route('notifications.index')); ?>" class="text-primary small text-decoration-none">Lihat Semua</a>
                        </div>
                        <div id="notifContainer" class="p-2">
                            <div class="text-center py-3 text-muted small">
                                <i class="fa-regular fa-bell-slash d-block mb-2"></i>
                                Tidak ada notifikasi baru
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 radius-md" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar-circle" style="background:linear-gradient(135deg,#2563eb,#38bdf8);color:#fff;font-weight:800;">
                            <?php echo e(strtoupper(substr(Auth::user()->nama ?? 'US', 0, 2))); ?>

                        </span>
                        <div class="d-none d-md-block text-start">
                            <div class="text-12 fw-bold lh-1"><?php echo e(Auth::user()->nama ?? 'Guest User'); ?></div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 mt-1" style="font-size: 9px; font-weight: 600; letter-spacing: 0.3px;"><?php echo e(session('jabatan') ?? session('role') ?? 'No Role'); ?></span>

                        </div>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 240px;">
                        <li>
                            <a class="dropdown-item" href="/profile">
                                <i class="fa-solid fa-user-pen me-2"></i> Edit Profil
                            </a>
                        </li>

                        <?php if(session('user_roles') && count(session('user_roles')) > 1): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header fw-bold text-uppercase small" style="font-size:10px; letter-spacing:0.5px;">Ganti Peran</h6></li>
                        <?php $__currentLoopData = session('user_roles'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <form action="/switch-role" method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="role_id" value="<?php echo e($item['id_access']); ?>">
                                <button type="submit"
                                    class="dropdown-item d-flex align-items-center gap-2 py-2 <?php echo e(session('role') == $item['role'] && session('kode_area') == $item['kode_area'] ? 'active' : ''); ?>"
                                    style="border:0; background:transparent; width:100%; text-align:left;">
                                    <span style="display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; background:#f3f4f6; font-size:10px;">
                                        <i class="fa-solid fa-arrows-rotate"></i>
                                    </span>
                                    <div>
                                        <div style="font-size:12px; font-weight:600; line-height:1.2;"><?php echo e($item['jabatan']); ?></div>
                                        <div style="font-size:10px; color:#6b7280; line-height:1.2;"><?php echo e($item['kode_area']); ?><?php if($item['kode_project']): ?> — <?php echo e($item['kode_project']); ?><?php endif; ?></div>
                                    </div>
                                </button>
                            </form>
                        </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>

                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="/logout" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="dropdown-item text-danger" style="background:transparent; border:0; width:100%; text-align:left;">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="page-content">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
</div>

<?php echo $__env->yieldPushContent('modals'); ?>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" style="z-index: 9999;"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\cdb-finance\resources\views/layouts/app.blade.php ENDPATH**/ ?>