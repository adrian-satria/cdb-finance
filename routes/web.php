<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\BudgetController; 
use App\Http\Controllers\Admin\UserController; 
use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\ActivityController;

// =========================================================================
// ROUTE PUBLIC (Bisa diakses sebelum login)
// =========================================================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:5,1'); // Rate limit: 5 attempts per 1 menit

require __DIR__ . '/web_profile.php';


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

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index']);

    // --- TRANSAKSI SPP (Akses Dikontrol State Machine di Controller) ---
    Route::get('/spp', [SppController::class, 'index']);

    // --- kelola surat (Super review) ---
    Route::get('/spp/kelola', [App\Http\Controllers\SppController::class, 'kelola'])->middleware('auth');
    Route::get('/spp/tambah', [SppController::class, 'create']);
    Route::post('/spp/simpan', [SppController::class, 'store']); // Disamakan pakai /simpan sesuai view form-mu
    Route::post('/spp/validasi', [SppController::class, 'validasi']);
    Route::post('/spp/cairkan', [SppController::class, 'cairkan']);
    
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

    require __DIR__ . '/admin.php';

    // Admin access management (user_access)


        // (routes for access management moved to routes/admin.php)
        
        // --- CRUD Master Budget (Aman & Terpantau) ---

        Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');
        Route::get('/budget/create', [BudgetController::class, 'create'])->name('budget.create');
        Route::post('/budget/store', [BudgetController::class, 'store'])->name('budget.store');
        Route::get('/budget/{id}/edit', [BudgetController::class, 'edit'])->name('budget.edit');
        Route::put('/budget/{id}/update', [BudgetController::class, 'update'])->name('budget.update');
        Route::delete('/budget/{id}/delete', [BudgetController::class, 'destroy'])->name('budget.destroy');

        // --- Import Master Budget (CSV) ---
        Route::get('/budget/import', [\App\Http\Controllers\Admin\BudgetImportController::class, 'showForm'])->name('budget.import.form');
        Route::post('/budget/import', [\App\Http\Controllers\Admin\BudgetImportController::class, 'import'])->name('budget.import');

        // --- CRUD Kelola User ---
        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
        Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/user/{id}/update', [UserController::class, 'update'])->name('user.update');
        Route::delete('/user/{id}/delete', [UserController::class, 'destroy'])->name('user.destroy');

        // --- Sistem Audit Trail Forensik ---
        Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit_trail.index');

        // --- REPORTING MODULE ---
        Route::get('/reports/budget-vs-actual', [ReportController::class, 'budgetVsActual'])->name('reports.budget_vs_actual');
        Route::get('/reports/financial-summary', [ReportController::class, 'financialSummary'])->name('reports.financial_summary');
        Route::get('/reports/area-performance', [ReportController::class, 'areaPerformance'])->name('reports.area_performance');

        // --- SYSTEM SETTINGS ---
        Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/update', [SystemSettingController::class, 'update'])->name('admin.settings.update');
        Route::post('/settings/create-default', [SystemSettingController::class, 'createDefault'])->name('admin.settings.create-default');
        Route::post('/settings/clear-cache', [SystemSettingController::class, 'clearCache'])->name('admin.settings.clear-cache');

        // --- ACTIVITY MONITORING ---
        Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
        Route::get('/activity/online-users', [ActivityController::class, 'onlineUsers'])->name('admin.activity.online');
    });

    // --- PROSES LOGOUT ---
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- DASHBOARD UTAMA / WELCOME ---
    Route::get('/', function () {
        return view('welcome');
    });
});