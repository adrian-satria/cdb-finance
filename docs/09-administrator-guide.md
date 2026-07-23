# 09 — Administrator Guide

## 9.1 Mengelola User

**Menu:** Data Master → Kelola User

### Tambah User Baru
1. Klik **Tambah User Baru**
2. Isi:
   - **Nama** — nama lengkap
   - **Username** — digunakan untuk login
   - **Password** — min 8 karakter, kombinasi huruf besar + kecil + angka

### Edit User
1. Klik ikon **pensil** pada user
2. Ubah nama/username
3. Isi password hanya jika ingin mengganti

### Hapus User
1. Klik ikon **tong sampah**
2. Konfirmasi hapus

> ⚠️ Menghapus user tidak menghapus `user_access` terkait secara otomatis.

## 9.2 Mengatur Akses Role

**Menu:** Setelah buka user → klik **Kelola Akses**

> [INSERT SCREENSHOT: user-access.png]

1. Pilih **Role** dari dropdown (17 role tersedia)
2. Pilih **Area** (unit kerja, untuk role non-project)
3. Pilih **Project** (untuk role project-scoped)
4. Isi **Jabatan** (nama jabatan organisasi)
5. Klik Simpan

**Multi Role:** Satu user bisa memiliki banyak kombinasi role + area + project.

**Project Multiple:** Untuk memberikan akses ke beberapa project sekaligus, gunakan format: `38,40,01` (pisahkan koma).

## 9.3 Master Budget

**Menu:** Data Master → Master Budget

### Tambah Budget
1. Klik **Tambah Budget**
2. Isi: Project, Kode Budget, Nama Budget, Alokasi Dana
3. Simpan

### Import CSV
1. Klik **Import CSV**
2. Upload file CSV dengan format:

```csv
kode_project,kode_budget,nama_budget,alokasi_dana
38,BDGT001,Anggaran Operasional,100000000
40,BDGT002,Anggaran Program,50000000
```

3. Pemisah: koma (`,`) atau titik koma (`;`) — auto-detect
4. Kolom `nama_budget` opsional (jika ada, akan update)
5. Data di-upsert berdasarkan `(kode_project, kode_budget)`

> ⚠️ Setelah import, cek log audit untuk verifikasi jumlah insert/update/skip

## 9.4 Audit Trail

**Menu:** Audit Trail Log

> [INSERT SCREENSHOT: audit-trail.png]

Semua perubahan tercatat:
| Kolom | Keterangan |
|-------|------------|
| Timestamp | Waktu kejadian |
| Username | Pelaku |
| Role | Role saat aksi |
| Aksi | Tipe: INSERT_SPP, APPROVE_AREA, CETAK_PDF, dll |
| IP Address | IP pelaku |
| User Agent | Browser/device |

Data disimpan permanen — tidak bisa dihapus via UI.

## 9.5 System Settings

**Menu:** Pengaturan Sistem

Pengaturan key-value yang bisa diubah:

| Key | Default | Fungsi |
|-----|---------|--------|
| app_name | B-SMART | Nama aplikasi |
| max_file_size | 5120 | Max upload (KB) |
| session_timeout | 120 | Timeout session (menit) |
| password_min_length | 8 | Min panjang password |
| login_attempt_limit | 5 | Maks percobaan login |
| maintenance_mode | false | Mode maintenance |
| enable_notification | true | Aktifkan notifikasi |

**Tombol:**
- **Clear Cache** — bersihkan cache aplikasi

## 9.6 Laporan

**Menu:** Laporan → (3 laporan)

### Budget vs Actual
Perbandingan alokasi vs realisasi per project.
> [INSERT SCREENSHOT: report-bva.png]

### Ringkasan Keuangan
Total budget, terserap, sisa, utilisasi.
> [INSERT SCREENSHOT: report-financial.png]

### Performa Area
Statistik per unit kerja.
> [INSERT SCREENSHOT: report-area.png]

## 9.7 Monitor Aktivitas

**Menu:** Monitor Aktivitas

Menampilkan real-time aktivitas user:
- User online saat ini
- Aktivitas terakhir per user
- Session ID

## 9.8 Restore Data (Import SQL)

Jika perlu import database dari lingkungan lain:
1. Export SQL dari sumber tanpa `CREATE DATABASE`
2. Ganti collation `utf8mb4_0900_ai_ci` → `utf8mb4_general_ci`
3. Import via phpMyAdmin hPanel
4. Jalankan `php artisan migrate` untuk update
