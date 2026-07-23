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
| Validasi SPP | ✅** | - | ✅* | ✅* | ✅* | ✅* | ✅* | - | ✅* |
| Pencairan Dana | ✅ | - | - | - | - | - | - | ✅ | - |
| Preview/Cetak PDF | ✅ | ✅* | ✅* | ✅* | ✅* | ✅ | ✅ | ✅ | - |
| Notifikasi | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

Keterangan:
- ✅* = Scoped by area/project
- ✅** = ADMIN bisa approve sebagai role manapun (bypass)
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
1. Login → if user punya >1 role → redirect ke halaman **pilih_peran**
2. Pilih role → session di-set dengan role, area, project
3. Bisa **switch role** kapan saja dari dropdown profil
4. Setiap request dicek: `ValidateSession` membandingkan session role dengan DB
5. Jika role tidak valid lagi → logout paksa + log `PRIVILEGE_ESCALATION_ATTEMPT`

## 5.6 Admin Bypass Workflow

ADMIN dapat melakukan validasi pada SPP yang berada di posisi mana pun:

```php
// SppController::validasi()
$effectiveRole = ($currentRole === 'ADMIN')
    ? $surat->posisi_saat_ini
    : $currentRole;
```

Ini memungkinkan ADMIN menyelesaikan SPP yang stuck di suatu posisi.

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
