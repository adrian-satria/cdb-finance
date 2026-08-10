<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\Admin\BudgetController;
use App\Http\Controllers\Admin\BudgetImportController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SppController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// ROUTE PUBLIC (Bisa diakses sebelum login)
// =========================================================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:5,1'); // Rate limit: 5 attempts per 1 menit

require __DIR__.'/web_profile.php';

// =========================================================================
// ROUTE TERPROTEKSI (Wajib Login - Tapi belum wajib punya role)
// =========================================================================
Route::middleware(['auth'])->group(function () {
    // Otorisasi pemilihan peran (Set Peran) - user multi-role belum punya session('role')
    Route::post('/set-peran', [AuthController::class, 'setPeran']);
});

// =========================================================================
// ROUTE TERPROTEKSI FULL (Wajib Login + Session Valid + Punya Role)
// =========================================================================
Route::middleware(['auth', 'validate.session'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- TRANSAKSI SPP (Akses Dikontrol State Machine di Controller) ---
    Route::get('/spp', [SppController::class, 'index']);

    // --- kelola surat (Super review) ---
    Route::get('/spp/kelola', [SppController::class, 'kelola']);
    Route::get('/spp/tambah', [SppController::class, 'create']);
    Route::post('/spp/simpan', [SppController::class, 'store'])->middleware('throttle:30,1');
    Route::post('/spp/validasi', [SppController::class, 'validasi'])->middleware('throttle:30,1');
    Route::post('/spp/cairkan', [SppController::class, 'cairkan'])->middleware('throttle:10,1');

    // Secure Dokumen & API Internal Detail
    Route::get('/spp/detail-items', [SppController::class, 'getDetailItems']);
    Route::get('/spp/preview-cetak', [SppController::class, 'previewPdf']);
    Route::get('/spp/cetak', [SppController::class, 'cetakPdf']);
    Route::get('/spp/file/{nama_file}', [SppController::class, 'downloadFile']);

    // --- NOTIFIKASI ---
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/fetch-unread', [NotificationController::class, 'fetchUnread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // =========================================================================
    // MODUL GRUP ADMIN B-SMART (DIKUNCI KETAT DENGAN MIDDLEWARE role:ADMIN)
    // =========================================================================
    Route::middleware(['role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {

        require __DIR__.'/admin.php';

        // Admin access management (user_access)

        // (routes for access management moved to routes/admin.php)

        // --- CRUD Project ---
        Route::get('/project', [ProjectController::class, 'index'])->name('project.index');
        Route::get('/project/create', [ProjectController::class, 'create'])->name('project.create');
        Route::post('/project/store', [ProjectController::class, 'store'])->name('project.store')->middleware('throttle:30,1');
        Route::get('/project/{project}/edit', [ProjectController::class, 'edit'])->name('project.edit');
        Route::put('/project/{project}/update', [ProjectController::class, 'update'])->name('project.update')->middleware('throttle:30,1');
        Route::delete('/project/{project}/delete', [ProjectController::class, 'destroy'])->name('project.destroy')->middleware('throttle:30,1');

        // --- CRUD Area ---
        Route::get('/area', [AreaController::class, 'index'])->name('area.index');
        Route::get('/area/create', [AreaController::class, 'create'])->name('area.create');
        Route::post('/area/store', [AreaController::class, 'store'])->name('area.store')->middleware('throttle:30,1');
        Route::get('/area/{area}/edit', [AreaController::class, 'edit'])->name('area.edit');
        Route::put('/area/{area}/update', [AreaController::class, 'update'])->name('area.update')->middleware('throttle:30,1');
        Route::delete('/area/{area}/delete', [AreaController::class, 'destroy'])->name('area.destroy')->middleware('throttle:30,1');

        // --- Budget Per Area ---
        Route::get('/budget/{id}/area', [BudgetController::class, 'areaForm'])->name('budget.area');
        Route::post('/budget/{id}/area/store', [BudgetController::class, 'areaStore'])->name('budget.area.store')->middleware('throttle:30,1');

        // --- CRUD Master Budget (Aman & Terpantau) ---

        Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');
        Route::get('/budget/create', [BudgetController::class, 'create'])->name('budget.create');
        Route::post('/budget/store', [BudgetController::class, 'store'])->name('budget.store')->middleware('throttle:30,1');
        Route::get('/budget/{id}/edit', [BudgetController::class, 'edit'])->name('budget.edit');
        Route::put('/budget/{id}/update', [BudgetController::class, 'update'])->name('budget.update')->middleware('throttle:30,1');
        Route::delete('/budget/{id}/delete', [BudgetController::class, 'destroy'])->name('budget.destroy')->middleware('throttle:30,1');

        // --- Import Master Budget (CSV) ---
        Route::get('/budget/import', [BudgetImportController::class, 'showForm'])->name('budget.import.form');
        Route::post('/budget/import', [BudgetImportController::class, 'import'])->name('budget.import')->middleware('throttle:10,1');

        // --- CRUD Kelola User ---
        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/user/store', [UserController::class, 'store'])->name('user.store')->middleware('throttle:10,1');
        Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/user/{id}/update', [UserController::class, 'update'])->name('user.update')->middleware('throttle:10,1');
        Route::delete('/user/{id}/delete', [UserController::class, 'destroy'])->name('user.destroy')->middleware('throttle:10,1');

        // --- Sistem Audit Trail Forensik ---
        Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit_trail.index');

        // --- REPORTING MODULE ---
        Route::get('/reports/budget-vs-actual', [ReportController::class, 'budgetVsActual'])->name('reports.budget_vs_actual');
        Route::get('/reports/financial-summary', [ReportController::class, 'financialSummary'])->name('reports.financial_summary');
        Route::get('/reports/area-performance', [ReportController::class, 'areaPerformance'])->name('reports.area_performance');

        // --- SYSTEM SETTINGS ---
        Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/update', [SystemSettingController::class, 'update'])->name('settings.update')->middleware('throttle:30,1');
        Route::post('/settings/create-default', [SystemSettingController::class, 'createDefault'])->name('settings.create-default')->middleware('throttle:10,1');
        Route::post('/settings/clear-cache', [SystemSettingController::class, 'clearCache'])->name('settings.clear-cache')->middleware('throttle:10,1');

        // --- ACTIVITY MONITORING ---
        Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
        Route::get('/activity/online-users', [ActivityController::class, 'onlineUsers'])->name('activity.online');
    });

    // --- SWITCH ROLE ---
    Route::post('/switch-role', [AuthController::class, 'switchRole']);

    // --- PROSES LOGOUT ---
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- DASHBOARD UTAMA / WELCOME ---
    Route::get('/', function () {
        return view('welcome');
    });
});
