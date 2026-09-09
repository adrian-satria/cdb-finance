# 01 — System Overview

## 1.1 Deskripsi

**Finance Management Demo** adalah aplikasi manajemen pengajuan dana dan pembayaran yang dikembangkan sebagai portfolio project berbasis Laravel.

Aplikasi ini mensimulasikan workflow pengajuan dana mulai dari pembuatan pengajuan, proses approval bertahap, validasi pagu anggaran, pencairan, hingga pertanggungjawaban dan pelaporan.

Sistem menggunakan role-based access control, project/area scoping, audit trail, dan validasi anggaran untuk membantu menjaga proses keuangan tetap terkontrol dan terdokumentasi.

## 1.2 Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| Manajemen Pengajuan Dana | Buat, edit, validasi, revisi, tolak, dan proses pencairan pengajuan |
| Workflow Approval | State machine multi-tahap dengan beberapa tipe alur approval |
| Budget Ceiling | Validasi pagu anggaran otomatis dengan pessimistic locking |
| Role-Based Access | Akses pengguna dibatasi berdasarkan role dan scope |
| Multi-Role User | Satu user dapat memiliki beberapa kombinasi role dan scope |
| Audit Trail | Perubahan penting tercatat beserta pelaku dan waktu kejadian |
| Notifikasi | Notifikasi in-app untuk event tertentu dalam workflow |
| File Upload | Lampiran dokumen pendukung dalam format PDF/gambar |
| PDF Generation | Generate dokumen pengajuan dan pertanggungjawaban dalam format PDF |
| Dashboard | Statistik, grafik, dan task list berdasarkan akses pengguna |

## 1.3 Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP 8.2+, Laravel 11.x |
| Frontend | Blade, Bootstrap 5, Vite, Vanilla JavaScript |
| Database | MySQL |
| PDF | barryvdh/laravel-dompdf |
| Authentication | Laravel session-based authentication |
| Session | File / Database driver |
| Cache | Database / File |
| Queue | Database |

## 1.4 Role & Access Model

Aplikasi menerapkan role-based access control dengan scope yang dapat dibatasi berdasarkan area maupun project.

| Role | Scope | Tanggung Jawab |
|------|-------|----------------|
| ADMIN | Global | Administrasi dan konfigurasi sistem |
| FINANCE_MANAGER | Global | Review dan approval keuangan |
| CASHIER | Global | Proses pencairan dana |
| MAKER | Area | Membuat pengajuan |
| AREA_MANAGER | Area | Review dan approval pengajuan area |
| FINANCE_REVIEWER | Project | Review aspek keuangan |
| PROJECT_MANAGER | Project | Review dan approval project |
| FINANCE_COORDINATOR | Project | Approval tahap awal |

Model akses ini memungkinkan satu pengguna memiliki beberapa role dengan kombinasi scope yang berbeda.

## 1.5 Struktur Direktori

```text
app/
├── Http/
│   ├── Controllers/        # Application & admin controllers
│   ├── Middleware/         # Session, role & security middleware
│   └── Requests/           # FormRequest validation
├── Models/                 # Eloquent models
├── Observers/              # Model observers
├── Providers/              # Application service providers
├── Rules/                  # Custom validation rules
├── Services/               # Business logic & application services
├── Support/                # Helper classes
└── ValueObjects/           # Domain value objects

config/                     # Application configuration

database/
├── migrations/             # Database schema migrations
├── factories/              # Model factories
└── seeders/                # Demo data seeders

resources/
├── views/                  # Blade templates
├── css/                    # CSS modules
└── js/                     # JavaScript modules

routes/                     # Application route definitions

public/
├── build/                  # Vite production assets
└── images/                 # Application assets

## 1.6 Workflow Types

Aplikasi mendukung beberapa tipe workflow untuk menyesuaikan kebutuhan proses approval.

Tipe Flow	Deskripsi
PROJECT_FLOW	Workflow approval untuk pengajuan berbasis project
STANDARD_FLOW	Workflow approval umum
SPECIALIZED_FLOW	Workflow dengan tahapan approval khusus

Detail workflow dapat dilihat pada dokumen 04-workflow.md.