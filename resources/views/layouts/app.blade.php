<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CDB Finance - B-SMART')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=300;400;500;600;700;800&family=DM+Sans:wght=300;400;500;600;700&display=swap');

        :root {
          --primary:        #1a4f8a;
          --primary-light:  #2563eb;
          --primary-dark:   #0f2d5a;
          --accent:         #38bdf8;
          --accent-soft:    #e0f2fe;
          --success:        #10b981;
          --warning:        #f59e0b;
          --danger:         #ef4444;
          --info:           #06b6d4;

          --bg-base:        #f0f4f8;
          --bg-white:       #ffffff;
          --bg-sidebar:     #0f1f3d;
          --text-primary:   #1e293b;
          --text-secondary: #64748b;
          --text-muted:     #94a3b8;
          --border:         #e2e8f0;
          --border-light:   #f1f5f9;

          --sidebar-width:  260px;
          --sidebar-collapsed: 70px;
          --radius-md:  10px;
          --radius-lg:  14px;
          --transition: all .2s cubic-bezier(.4,0,.2,1);
          --font-main:  'Plus Jakarta Sans', sans-serif;
          --font-body:  'DM Sans', sans-serif;
          --shadow-card: 0 2px 12px rgba(15,45,90,.08);
        }

        body {
          font-family: var(--font-body);
          background: var(--bg-base);
          color: var(--text-primary);
        }

        .app-wrapper { display: flex; min-height: 100vh; }

        .sidebar {
          position: fixed; top: 0; left: 0;
          width: var(--sidebar-width); height: 100vh;
          background: var(--bg-sidebar); display: flex; flex-direction: column;
          z-index: 1000; transition: width .3s; overflow: hidden;
        }
        .sidebar.collapsed { width: var(--sidebar-collapsed); }

        .sidebar-brand {
          display: flex; align-items: center; gap: 12px; padding: 20px 16px;
          border-bottom: 1px solid rgba(255,255,255,.07); min-height: 72px; text-decoration: none;
        }
        .sidebar-logo {
          width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary-light), var(--accent));
          border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;
          font-family: var(--font-main); font-weight: 800; color: #fff;
        }
        .sidebar-app-name { font-family: var(--font-main); font-weight: 800; font-size: 15px; color: #fff; }

        .sidebar-nav { flex: 1; padding: 12px 0; }
        .nav-section-label { font-size: 10px; font-weight: 700; color: rgba(255,255,255,.25); text-transform: uppercase; padding: 10px 20px 4px; }

        .sidebar-item {
          display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; margin: 2px 8px;
          border-radius: var(--radius-md); color: rgba(255,255,255,.6); text-decoration: none; transition: var(--transition); cursor: pointer;
        }
        .sidebar-item:hover { background: rgba(255,255,255,.07); color: #fff; }
        .sidebar-item.active { background: linear-gradient(135deg, var(--primary-light), #1d4ed8); color: #fff; }
        
        .sidebar-item .nav-icon { width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: rgba(255,255,255,.07); margin-right: 6px; }

        .sidebar-child-menu a {
          display: flex; align-items: center; padding: 10px 16px 10px 48px; margin: 2px 8px;
          border-radius: var(--radius-md); color: rgba(255,255,255,.5); text-decoration: none; font-size: 13px;
        }
        .sidebar-child-menu a:hover { color: #fff; background: rgba(255,255,255,.04); }
        .sidebar-child-menu a.active { color: #fff; background: rgba(255,255,255,.08); }

        .sidebar-item[aria-expanded="true"] .toggle-icon { transform: rotate(180deg); }
        .toggle-icon { transition: transform .2s; }

        .sidebar-footer { padding: 12px 8px; border-top: 1px solid rgba(255,255,255,.07); }
        .sidebar-user { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: var(--radius-md); background: rgba(255,255,255,.05); cursor: pointer; }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-light), var(--accent)); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; }

        .main-content { margin-left: var(--sidebar-width); flex: 1; display: flex; flex-direction: column; min-height: 100vh; transition: margin-left .3s; position: relative; z-index: 1; width: calc(100% - var(--sidebar-width)); }
        .main-content.expanded { margin-left: var(--sidebar-collapsed); width: calc(100% - var(--sidebar-collapsed)); }

        .topbar { position: sticky; top: 0; z-index: 500; background: rgba(240,244,248,.92); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); padding: 0 24px; height: 64px; display: flex; align-items: center; gap: 16px; }
        .topbar-toggle { width: 36px; height: 36px; border: none; background: #fff; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary); }
        .page-content { flex: 1; padding: 24px; position: relative; z-index: 250; }
        .badge-primary-custom { background: #eff6ff; color: var(--primary-light); font-weight: 600; padding: 4px 10px; border-radius: 99px; }

        .sidebar {
            width: 260px;
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar.collapsed {
            width: 72px;
        }
        .sidebar.collapsed .sidebar-app-name,
        .sidebar.collapsed .nav-section-label,
        .sidebar.collapsed .nav-label,
        .sidebar.collapsed .toggle-icon,
        .sidebar.collapsed .user-info,
        .sidebar.collapsed .sidebar-brand .sidebar-title-wrap {
            display: none !important;
        }
        .sidebar.collapsed .sidebar-item {
            justify-content: center;
            padding: 12px 0 !important;
            border-radius: 16px;
            margin: 4px 12px;
        }
        .sidebar.collapsed .sidebar-item .d-flex {
            justify-content: center;
            width: 100%;
        }
        .sidebar.collapsed .nav-icon {
            margin: 0 !important;
            font-size: 18px;
        }
        .sidebar.collapsed .sidebar-item.active {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px auto;
        }
        .sidebar.collapsed .sidebar-child-menu {
            display: none !important;
        }
        .sidebar.collapsed .sidebar-footer {
            padding: 12px 0;
        }
        .sidebar.collapsed .sidebar-user {
            justify-content: center;
            padding: 0;
        }
        .sidebar.collapsed .sidebar-user .ms-auto {
            display: none !important;
        }
    </style>
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
<script>
    document.getElementById('toggleSidebarBtn').addEventListener('click', function() {
        document.getElementById('sidebarMenu').classList.toggle('collapsed');
        document.getElementById('mainContent').classList.toggle('expanded');
    });

    // Fetch notifications
    function fetchNotifications() {
        fetch('/notifications/fetch-unread')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notifBadge');
                const container = document.getElementById('notifContainer');
                
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                    
                    let html = '';
                    data.notifications.forEach(n => {
                        html += `
                            <div class="dropdown-item px-2 py-2 border-bottom" style="cursor: pointer; font-size: 12px;" onclick="markAsRead(${n.id})">
                                <div class="fw-semibold text-dark">${n.title}</div>
                                <div class="text-muted small">${n.message}</div>
                                <div class="text-muted mt-1" style="font-size: 10px;">${n.created_at}</div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(err => console.error('Failed to fetch notifications:', err));
    }

    function markAsRead(id) {
        fetch('/notifications/' + id + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            fetchNotifications();
        });
    }

    // Fetch on load and every 30 seconds
    fetchNotifications();
    setInterval(fetchNotifications, 30000);
</script>
@stack('scripts')
</body>
</html>
