<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CDB Finance - B-SMART')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body>

<div class="app-wrapper">
    <div class="sidebar" id="sidebarMenu">
        <a href="/spp" class="sidebar-brand">
            <div class="sidebar-logo">BS</div>
            <div class="sidebar-title-wrap">
                <span class="sidebar-app-name">B-SMART</span>
            </div>
        </a>
        
        <div class="sidebar-nav">
            @if(session('role') != 'ADMIN')
            <div class="nav-section-label">Main System</div>

            <a href="/dashboard" class="sidebar-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <div class="d-flex align-items-center">
                    <div class="nav-icon"><i class="fa-solid fa-gauge-high"></i></div>
                    <span class="nav-label">Dashboard</span>
                </div>
            </a>
            
            <a href="#sppSubMenu" data-bs-toggle="collapse" aria-expanded="{{ request()->is('spp*') ? 'true' : 'false' }}" class="sidebar-item {{ request()->is('spp*') ? 'active' : '' }}">
                <div class="d-flex align-items-center">
                    <div class="nav-icon"><i class="fa-solid fa-wallet"></i></div>
                    <span class="nav-label">Transaksi SPP</span>
                </div>
                <i class="fa-solid fa-chevron-down toggle-icon fs-7"></i>
            </a>
            
            <div class="collapse sidebar-child-menu {{ request()->is('spp*') ? 'show' : '' }}" id="sppSubMenu">
                <a href="/spp" class="{{ request()->is('spp') ? 'active' : '' }}">
                    <i class="fa-solid fa-table-list me-2"></i>Daftar Data SPP
                </a>

                <a href="/spp/tambah" class="{{ request()->is('spp/tambah') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-circle-plus me-2"></i>Input SPP Baru
                </a>


                @if(session('role') == 'MANAGER_KEUANGAN')
                <a href="/spp/kelola" class="{{ request()->is('spp/kelola') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-check me-2"></i>Kelola Surat
                </a>
                @endif
            </div>
            @endif

            @if(session('role') == 'ADMIN')
            <div class="nav-section-label mt-3">Fitur Admin</div>

            <a href="/dashboard" class="sidebar-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <div class="d-flex align-items-center">
                    <div class="nav-icon"><i class="fa-solid fa-gauge-high"></i></div>
                    <span class="nav-label">Dashboard</span>
                </div>
            </a>

            <a href="#adminSubMenu" data-bs-toggle="collapse" aria-expanded="{{ request()->is('admin*') ? 'true' : 'false' }}" class="sidebar-item {{ request()->is('admin*') ? 'active' : '' }}">
                <div class="d-flex align-items-center">
                    <div class="nav-icon"><i class="fa-solid fa-sliders"></i></div>
                    <span class="nav-label">Data Master</span>
                </div>
                <i class="fa-solid fa-chevron-down toggle-icon fs-7"></i>
            </a>

            <div class="collapse sidebar-child-menu {{ request()->is('admin*') ? 'show' : '' }}" id="adminSubMenu">
                <a href="/admin/budget" class="{{ request()->is('admin/budget*') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-tree me-2"></i>Master Budget
                </a>
                <a href="/admin/user" class="{{ request()->is('admin/user*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-gear me-2"></i>Kelola User
                </a>
            </div>

            <a href="/admin/audit-trail" class="sidebar-item {{ Request::is('admin/audit-trail') ? 'active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-user-secret nav-icon me-3"></i>
                    <span class="nav-label">Audit Trail Log</span>
                </div>
            </a>

            <a href="#reportsSubMenu" data-bs-toggle="collapse" aria-expanded="{{ request()->is('admin/reports*') ? 'true' : 'false' }}" class="sidebar-item {{ request()->is('admin/reports*') ? 'active' : '' }}">
                <div class="d-flex align-items-center">
                    <div class="nav-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <span class="nav-label">Laporan</span>
                </div>
                <i class="fa-solid fa-chevron-down toggle-icon fs-7"></i>
            </a>

            <div class="collapse sidebar-child-menu {{ request()->is('admin/reports*') ? 'show' : '' }}" id="reportsSubMenu">
                <a href="{{ route('admin.reports.budget_vs_actual') }}" class="{{ request()->is('admin/reports/budget-vs-actual') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-bar me-2"></i>Budget vs Actual
                </a>
                <a href="{{ route('admin.reports.financial_summary') }}" class="{{ request()->is('admin/reports/financial-summary') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice me-2"></i>Ringkasan Keuangan
                </a>
                <a href="{{ route('admin.reports.area_performance') }}" class="{{ request()->is('admin/reports/area-performance') ? 'active' : '' }}">
                    <i class="fa-solid fa-map-location-dot me-2"></i>Performa Area
                </a>
            </div>

            <a href="{{ route('admin.activity.index') }}" class="sidebar-item {{ Request::is('admin/activity') ? 'active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-activity nav-icon me-3"></i>
                    <span class="nav-label">Monitor Aktivitas</span>
                </div>
            </a>

            <a href="{{ route('admin.settings.index') }}" class="sidebar-item {{ Request::is('admin/settings') ? 'active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-gear nav-icon me-3"></i>
                    <span class="nav-label">Pengaturan Sistem</span>
                </div>
            </a>
            @endif
        </div>
        
        <div class="sidebar-footer"></div>
    </div>

    <form id="logout-form" action="/logout" method="POST" class="d-none">@csrf</form>

    <div class="main-content" id="mainContent">
        <div class="topbar">
            <button class="topbar-toggle" id="toggleSidebarBtn"><i class="fa-solid fa-bars"></i></button>
            <div class="topbar-title ms-2">Unit Kerja: <span class="badge-primary-custom">{{ session('kode_area') ?? 'PUSAT' }}</span></div>

            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown" style="border-radius: 10px; padding: 8px 12px;">
                        <i class="fa-solid fa-bell text-secondary"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="font-size: 9px; display: none;">
                            0
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="min-width: 320px; max-height: 400px; overflow-y: auto; border-radius: 12px;">
                        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold" style="font-size: 13px;">Notifikasi</h6>
                            <a href="{{ route('notifications.index') }}" class="text-primary small text-decoration-none">Lihat Semua</a>
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
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 12px;">
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:50%; background: linear-gradient(135deg, #2563eb, #38bdf8); color:#fff; font-weight:800;">
                            {{ strtoupper(substr(Auth::user()->nama ?? 'US', 0, 2)) }}
                        </span>
                        <div class="d-none d-md-block text-start">
                            <div style="font-size:12px; font-weight:700; line-height:1;">{{ Auth::user()->nama ?? 'Guest User' }}</div>
                            <div style="font-size:10px; color:#6b7280; line-height:1;">{{ session('jabatan') ?? session('role') ?? 'No Role' }}</div>

                        </div>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 220px;">
                        <li>
                            <a class="dropdown-item" href="/profile">
                                <i class="fa-solid fa-user-pen me-2"></i> Edit Profil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="/logout" method="POST">
                                @csrf
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
            @yield('content')
        </div>
    </div>
</div>

@stack('modals')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@vite(['resources/js/app.js'])
@stack('scripts')
</body>
</html>
