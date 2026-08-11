# 05 — Role & Permission Matrix

## 5.1 Role Categories

| Kategori | Role | Karakteristik |
|----------|------|---------------|
| **Global** | ADMIN, DIREKTUR, KASIR_PUSAT | Akses semua data, tidak terbatas area/project |
| **Staff Area** | MAKER, AREA_MANAGER | Scoped by `kode_area` |
| **Project Scoped** | FINANCE_PROJECT, PROJECT_MANAGER, MANAGER_KEUANGAN, MANAGER_PKP, KOORDINATOR_* | Scoped by `kode_project` |

## 5.2 Matriks Akses per Fitur

| Fitur / Halaman | ADMIN | MAKER | AREA_MGR | FINANCE_PROJ | PROJ_MGR | MGR_KEU | DIREKTUR | KASIR | KOORD* |
|-----------------|-------|-------|----------|-------------|----------|---------|----------|-------|--------|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Data SPP (list) | ✅ | ✅* | ✅* | ✅* | ✅* | ✅ | ✅ | ✅ | ✅* |
| Input SPP Baru | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Kelola Surat | ✅ | - | - | - | - | ✅ | - | - | - |
| Validasi SPP | - | - | ✅* | ✅* | ✅* | ✅* | ✅* | - | ✅* |
| Pencairan Dana | - | - | - | - | - | - | - | ✅ | - |
| Preview/Cetak PDF | ✅ | ✅* | ✅* | ✅* | ✅* | ✅ | ✅ | ✅ | - |
| Notifikasi | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

Keterangan:
- ✅* = Scoped by area/project
- KOORD* = Semua KOORDINATOR_KEUANGAN, PK, TC, DIKLAT, KLINIK, BATRA, BIDANG

## 5.3 Matriks Akses Admin

| Fitur Admin | ADMIN |
|-------------|-------|
| Master Budget (CRUD + Import CSV) | ✅ |
| Kelola User (CRUD) | ✅ |
| User Access (atur role) | ✅ |
| Audit Trail | ✅ |
| Laporan (Budget vs Actual, Financial Summary, Area Performance) | ✅ |
| System Settings | ✅ |
| Monitor Aktivitas / Online Users | ✅ |
| Clear Cache | ✅ |

## 5.4 Data Scope per Role

### Scoping Logic (RoleHelper)

```php
class RoleHelper
{
    // Global: tidak ada filter
    const GLOBAL_ROLES = ['ADMIN', 'KASIR_PUSAT', 'DIREKTUR'];

    // Staff Area: where('kode_area', session('kode_area'))
    const STAFF_AREA_ROLES = ['MAKER', 'AREA_MANAGER'];

    // Project: where('kode_project', session('kode_project'))
    const PROJECT_SCOPED_ROLES = [
        'FINANCE_PROJECT', 'PROJECT_MANAGER',
        'MANAGER_KEUANGAN', 'MANAGER_PKP',
        'KOORDINATOR_KEUANGAN', 'KOORDINATOR_PK',
        'KOORDINATOR_TC', 'KOORDINATOR_DIKLAT',
        'KOORDINATOR_KLINIK', 'KOORDINATOR_BATRA',
        'KOORDINATOR_BIDANG',
    ];
}
```

### Query Scope

```php
// Contoh di SppController::index()
if (RoleHelper::isStaffArea($role)) {
    $query->where('kode_area', $kodeArea);
} elseif (RoleHelper::isProjectScoped($role)) {
    $query->where('kode_project', $userProject);
}
// Global role: no filter, lihat semua
```

### Scope Check untuk Download & Preview

```php
RoleHelper::canAccessSpp($role, $userArea, $userProject, $surat)
```
Digunakan di `downloadFile()`, `cetakPdf()`, `previewPdf()`, `getDetailItems()`.

## 5.5 Multi-Role Support

Satu user bisa memiliki banyak role (multiple rows di `user_access`).

**Flow:**
1. Login → role pertama paling atas di `user_access` diaktifkan otomatis saat login
2. Bisa **switch role** kapan saja dari dropdown profil
3. Setiap request dicek: `ValidateSession` membandingkan session role dengan DB
4. Jika role tidak valid lagi → logout paksa + log `PRIVILEGE_ESCALATION_ATTEMPT`

## 5.6 Admin sebagai Read-Only Auditor

**Sejak fix B2, ADMIN TIDAK bisa approve/revise/reject/cairkan SPP.** ADMIN adalah auditor read-only: bisa lihat semua data, super-review via `/spp/kelola`, preview/cetak SPP, akses arsip, dashboard, dan modul Admin (master data, audit trail, laporan, settings).

Penegakan di kode:

```php
// SppWorkflowService::validateWorkflowTransition()
if ($currentRole === 'ADMIN') {
    return false; // ADMIN read-only auditor, tidak bisa approve/revise/reject
}
```

Pencairan Dana juga dikunci eksplisit ke KASIR_PUSAT (bukan sekadar role global):

```php
// DisburseSppRequest::authorize()
public function authorize(): bool
{
    return session('role') === 'KASIR_PUSAT';
}
```

Scoping area/project untuk validasi dicek via `RoleHelper::canAccessSpp` di `SppController::validasi()` sebelum state machine dijalankan (fix B6).

## 5.7 Daftar 17 Role

| # | Role | Kategori |
|---|------|----------|
| 1 | ADMIN | Global |
| 2 | MAKER | Staff Area |
| 3 | AREA_MANAGER | Staff Area |
| 4 | FINANCE_PROJECT | Project |
| 5 | PROJECT_MANAGER | Project |
| 6 | MANAGER_KEUANGAN | Project |
| 7 | MANAGER_PKP | Project |
| 8 | KASIR_PUSAT | Global |
| 9 | DIREKTUR | Global |
| 10 | KOORDINATOR_KEUANGAN | Project |
| 11 | KOORDINATOR_PK | Project |
| 12 | KOORDINATOR_TC | Project |
| 13 | KOORDINATOR_DIKLAT | Project |
| 14 | KOORDINATOR_KLINIK | Project |
| 15 | KOORDINATOR_BATRA | Project |
| 16 | KOORDINATOR_BIDANG | Project |
