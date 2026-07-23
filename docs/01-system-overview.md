# 01 — System Overview

## 1.1 Deskripsi

**CDB Finance (B-SMART)** adalah sistem informasi manajemen pengajuan dana / Surat Permintaan Pembayaran (SPP) untuk UPKM/CD Bethesda YAKKUM. Sistem ini mengelola workflow pengajuan dana dari staf hingga pencairan oleh kasir, dengan kontrol otorisasi berbasis role dan pagu anggaran.

## 1.2 Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| Manajemen SPP | Buat, edit, validasi, revisi, tolak, cairkan pengajuan dana |
| Workflow Approval | State machine multi-tahap dengan 3 tipe alur berbeda |
| Budget Ceiling | Validasi pagu anggaran otomatis, lock pessimistic |
| Role-Based Access | 17 role dengan akses berbeda per fitur dan data |
| Multi-Role User | Satu user bisa memiliki banyak peran, bisa switch |
| Audit Trail | Semua perubahan tercatat: siapa, kapan, data sebelum/sesudah |
| Notifikasi | Notifikasi in-app untuk setiap event workflow |
| File Upload | Lampiran PDF/gambar untuk dokumen pendukung |
| PDF Generation | Cetak SPP resmi dengan tanda tangan digital |
| Dashboard | Statistik, grafik tren bulanan, task list per role |

## 1.3 Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP 8.2+, Laravel 11.x |
| Frontend | Blade, Bootstrap 5, Vite, vanilla JS (modular) |
| Database | MySQL (via PDO) |
| PDF | barryvdh/laravel-dompdf |
| Session | Database driver (File di production) |
| Cache | Database / File |
| Queue | Database |

## 1.4 Role Matrix

| Role | Tipe | Scope |
|------|------|-------|
| ADMIN | Global | Seluruh sistem |
| DIREKTUR | Global | Otorisasi >50jt |
| KASIR_PUSAT | Global | Pencairan dana |
| MAKER | Staff Area | Buat SPP (scoped by area) |
| AREA_MANAGER | Staff Area | Approve SPP area |
| FINANCE_PROJECT | Project | Approve finance |
| PROJECT_MANAGER | Project | Approve project |
| MANAGER_KEUANGAN | Project | Final review sebelum direktur |
| MANAGER_PKP | Project | Approve khusus alur PKP |
| KOORDINATOR_KEUANGAN | Project | Approve awal alus PO |
| KOORDINATOR_PK | Project | Approve awal alur PK |
| KOORDINATOR_TC | Project | Approve awal alur TC |
| KOORDINATOR_DIKLAT | Project | Approve awal alur DIKLAT |
| KOORDINATOR_KLINIK | Project | Approve awal alur KLINIK |
| KOORDINATOR_BATRA | Project | Approve awal alur BATRA |
| KOORDINATOR_BIDANG | Project | Approve awal alur BIDANG |

## 1.5 Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/        # 6 controllers utama + 8 admin
│   ├── Middleware/          # ValidateSession, CheckRole, SecurityHeaders
│   └── Requests/            # FormRequest validasi (Spp, Admin, Profile)
├── Models/                  # 13 Eloquent models
├── Observers/               # SppObserver
├── Providers/               # AppServiceProvider, ProfileViewServiceProvider
├── Rules/                   # Custom validation rules (3)
├── Services/                # 6 service classes
├── Support/                 # RoleHelper
└── ValueObjects/            # ValidationResult
config/                      # 12 config files
database/
├── migrations/              # 23 migration files
├── factories/
└── seeders/                 # 4 seeder files
resources/
├── views/                   # 33+ Blade templates
├── css/                     # 10 CSS modules
└── js/                      # 5 JS modules
routes/                      # web.php, admin.php, web_profile.php
public/
├── build/                   # Vite production assets
└── images/                  # Logo CD Bethesda
```

## 1.6 Workflow Types

| Tipe Flow | Project Codes |
|-----------|---------------|
| PROJECT_FLOW | 38, 40 |
| PO_PK_TC_FLOW | 01, 03, 07 |
| BATRA_KLINIK_DIKLAT_FLOW | 02, 04, 06 |

Detail lengkap ada di dokumen **04-workflow.md**.
