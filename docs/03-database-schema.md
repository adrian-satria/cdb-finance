# 03 — Database Schema

## 3.1 Entity Relationship

### Diagram Relasi

```
┌────────────────────────────────────────────────────────────┐
│                      users                                  │
│  PK id_user, username, password, nama, email,               │
│     signature_path, nama_lengkap                            │
└──────────┬─────────────────────────────────────┬────────────┘
           │ 1                                 1 │
           │                                     │
           ▼ n                                   ▼ n
┌──────────────────────────┐     ┌──────────────────────────────┐
│      user_access          │     │   surat_permintaan            │
│  PK id_access             │     │  PK no_surat                  │
│  FK id_user → users       │     │  FK id_maker → users          │
│     role, jabatan,        │     │     tanggal, kode_project,    │
│     kode_area,            │     │     kode_area, sumber_dana,   │
│     kode_project          │     │     total_nominal,            │
└──────────────────────────┘     │     status_surat,              │
                                 │     posisi_saat_ini,           │
                                 │     keterangan_checker         │
                                 └──────────┬────────────────────┘
                                            │ 1
                                            │
                            ┌───────────────┼────────────────┐
                            │               │                │
                            ▼ n             ▼ n              ▼ n
                 ┌──────────────────┐ ┌───────────┐ ┌──────────────┐
                 │ surat_permintaan │ │ surat_    │ │ spp_history   │
                 │ _detail          │ │ permintaan│ │               │
                 │ PK id            │ │ _files    │ │ PK id_history│
                 │ FK no_surat      │ │ PK id     │ │ FK no_surat  │
                 │ kode_budget,     │ │ FK no_surat││ status_dari/ke│
                 │ keterangan,      │ │ nama_file │ │ posisi_dari/ke│
                 │ nominal          │ │ kategori  │ │ aktor_username│
                 └──────────────────┘ │ tipe_file │ │ payload_before│
                                      └───────────┘ │ payload_after │
                                                    └──────────────┘

┌──────────────────────┐       ┌──────────────────────┐
│      master_budget    │       │      area              │
│  PK id_budget         │       │  PK kode_area          │
│     kode_project,     │       │     nama_area          │
│     kode_budget,      │       └──────────────────────┘
│     nama_budget,      │
│     alokasi_dana,     │       ┌──────────────────────┐
│     terserap,         │       │      project           │
│     tahun             │       │  PK kode_project       │
└──────────────────────┘       │     nama_project        │
                               │     FK kode_area        │
┌──────────────────────┐       └──────────────────────┘
│   audit_trails         │
│  PK id                 │       ┌──────────────────────┐
│     username, role,    │       │   sumber_dana         │
│     aksi, deskripsi,   │       │  PK id_bank_kas       │
│     payload_before,    │       │     nama_rekening,    │
│     ip_address,        │       │     no_rekening,      │
│     user_agent         │       │     bank, saldo       │
└──────────────────────┘       └──────────────────────┘
```

## 3.2 Detail Tabel

### users

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id (PK) | int | Auto increment (Laravel default) |
| id_user | int | Sama dengan id (digunakan relasi) |
| username | varchar(50) | Unique, digunakan login |
| password | varchar(255) | Bcrypt hash |
| nama | varchar(255) | Nama lengkap |
| name | varchar(255) | Nama (Laravel default) |
| email | varchar(255) | Auto-generated saat create |
| signature_path | varchar(255) | Path file tanda tangan di storage |
| nama_lengkap | varchar(255) | Duplikat nama |

### user_access

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id_access (PK) | int | Auto increment |
| id_user (FK) | int | → users.id_user |
| role | varchar(50) | ENUM: ADMIN, MAKER, AREA_MANAGER, dll (17 role) |
| jabatan | varchar(255) | Nama jabatan organisasi |
| kode_area | varchar(50) | Scope area kerja |
| kode_project | varchar(50) | Scope project (nullable) |

### surat_permintaan

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| no_surat (PK) | varchar(100) | Format: YYYY/Roman/SPP/PROJECT/XXX |
| tanggal | date | Tanggal SPP |
| jenis_permintaan | varchar(20) | SPP atau UM |
| kode_project | varchar(10) | → project.kode_project |
| kode_area | varchar(10) | → area.kode_area |
| sumber_dana | varchar(100) | Nama sumber dana |
| bank_tujuan | varchar(100) | Bank tujuan |
| no_rekening_tujuan | varchar(50) | Nomor rekening tujuan |
| nama_rekening_tujuan | varchar(100) | Nama pemilik rekening |
| total_nominal | decimal(15,2) | Total pengajuan |
| status_surat | varchar(50) | Pending / Approved / Rejected / Disbursed |
| posisi_saat_ini | varchar(50) | Role yang memegang otoritas saat ini |
| id_maker (FK) | int | → users.id_user (pembuat) |
| keterangan_checker | text | Catatan saat validasi |

### surat_permintaan_detail

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id (PK) | int | Auto increment |
| no_surat (FK) | varchar(100) | → surat_permintaan.no_surat |
| keterangan | text | Deskripsi item |
| kode_budget | varchar(100) | → master_budget.kode_budget |
| nominal | decimal(15,2) | Jumlah per item |

### surat_permintaan_files

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id (PK) | int | Auto increment |
| no_surat (FK) | varchar(100) | → surat_permintaan.no_surat |
| nama_file | varchar(255) | Nama unik file di storage |
| kategori | varchar(20) | MAKER atau CHECKER |
| tipe_file | varchar(10) | pdf, jpg, png |

### spp_history

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id_history (PK) | int | Auto increment |
| no_surat (FK) | varchar(100) | → surat_permintaan.no_surat |
| status_dari | varchar(50) | Status sebelum |
| status_ke | varchar(50) | Status sesudah |
| posisi_dari | varchar(50) | Posisi sebelum (role) |
| posisi_ke | varchar(50) | Posisi sesudah (role) |
| aktor_username | varchar(50) | Username yang melakukan aksi |
| aktor_role | varchar(50) | Role saat aksi |
| payload_before | json | Snapshot data sebelum |
| payload_after | json | Snapshot data sesudah |

### audit_trails

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id (PK) | int | Auto increment |
| username | varchar(50) | Pelaku |
| role | varchar(50) | Saat aksi |
| aksi | varchar(100) | Tipe aksi (INSERT_SPP, APPROVE_AREA, dll) |
| deskripsi | text | Detail aksi |
| payload_before | json | Data sebelum (opsional) |
| ip_address | varchar(45) | IP pelaku |
| user_agent | text | Browser/device |
| created_at | datetime | Waktu aksi |

### master_budget

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id_budget (PK) | int | Auto increment |
| kode_project | varchar(10) | → project.kode_project |
| kode_budget | varchar(100) | Kode unik anggaran |
| nama_budget | varchar(255) | Nama anggaran |
| alokasi_dana | decimal(15,2) | Pagu anggaran |
| terserap | decimal(15,2) | Realisasi yang sudah dipakai |
| tahun | year | Tahun anggaran |

### area

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| kode_area (PK) | varchar(10) | Kode unik area |
| nama_area | varchar(100) | Nama unit kerja |

### project

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| kode_project (PK) | varchar(10) | Kode unik project |
| nama_project | varchar(100) | Nama project |
| kode_area (FK) | varchar(10) | → area.kode_area |

### lainnya

| Tabel | PK | Keterangan |
|-------|-----|------------|
| notifications | id_notifikasi | Notifikasi in-app per user |
| activity_logs | id_activity | Log aktivitas user |
| sumber_dana | id_bank_kas | Data rekening bank sumber |
| sessions | id | Session store (DB driver) |
| system_settings | key | Key-value config |

## 3.3 Catatan Penting

- **No foreign key constraints** — relasi dijaga di aplikasi, bukan di database
- **Kolom `id` dan `id_user`** di tabel `users` — `id` adalah auto-increment Laravel, `id_user` nilainya sama digunakan untuk join
- **Charset** — `utf8mb4` (lokal), `utf8mb4_general_ci` (hosting)
- **Status SPP** — `Pending` → `Pending Director Otorisasi` → `Approved` → `Disbursed` | `Revisi` | `Rejected`
