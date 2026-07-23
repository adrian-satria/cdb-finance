# 08 — User Manual

## 8.1 Login

1. Buka `https://finance.cdbethesda.org/login`
2. Masukkan **Username** dan **Password**
3. Klik **Login Sekarang**

> [INSERT SCREENSHOT: login-page.png]

Jika gagal login 5 kali berturut-turut, akun akan di-lock sementara (rate limit).

## 8.2 Pilih Peran (Multi-Role User)

Jika user memiliki lebih dari satu peran, setelah login akan muncul halaman pemilihan peran:

1. Klik salah satu kartu peran sesuai tugas saat ini
2. System akan redirect ke dashboard dengan peran terpilih

> [INSERT SCREENSHOT: pilih-peran.png]

Untuk mengganti peran di tengah sesi:
1. Klik avatar profil (pojok kanan atas)
2. Pilih **Ganti Peran**
3. Pilih peran baru

## 8.3 Dashboard

> [INSERT SCREENSHOT: dashboard.png]

Dashboard menampilkan:
- **Total Budget** — pagu anggaran
- **Terserap** — realisasi terpakai
- **Sisa Budget** — sisa anggaran
- **Utilisasi** — persentase pemakaian (merah jika >90%)
- **Status Cards** — jumlah Pending, Approved, Rejected, Disbursed
- **My Tasks** — daftar SPP yang menunggu aksi dari role Anda
- **Budget per Project** (Admin) — progress bar setiap project
- **Tren Bulanan** — grafik jumlah pengajuan per bulan

## 8.4 Membuat SPP Baru (MAKER)

1. Klik menu **Transaksi SPP** → **Input SPP Baru**
2. Isi form:

| Field | Keterangan |
|-------|------------|
| Tanggal | Tanggal pengajuan |
| Project | Pilih project/ program |
| Sumber Dana | Nama rekening sumber |
| Tujuan Transfer | Bank, no rekening, nama penerima |
| Rincian Anggaran | Tambah item: kode budget, keterangan, nominal |
| Lampiran | Upload file pendukung (PDF/JPG/PNG, max 5MB, max 5 file) |

3. Klik **Simpan**

> [INSERT SCREENSHOT: spp-form.png]

Setelah tersimpan:
- Nomor SPP akan otomatis tergenerate (format: `YYYY/Roman/SPP/PROJECT/XXX`)
- SPP masuk antrian approval ke posisi pertama sesuai alur
- Notifikasi terkirim ke role yang berwenang

## 8.5 Melihat Riwayat SPP

Klik menu **Transaksi SPP** → **Data SPP**.

> [INSERT SCREENSHOT: spp-list.png]

Fitur:
- **Filter Project** — pilih project untuk filter
- **Sort** — klik header kolom (No Surat, Tanggal, Total, Status)
- **Pagination** — 20 data per halaman
- **Klik No Surat** — lihat rincian anggaran (modal)
- **Lampiran** — klik link untuk download file

## 8.6 Validasi SPP (Approver)

1. Buka **Data SPP**
2. Cari SPP dengan status **Pending** dan posisi sesuai role Anda
3. Klik tombol **Periksa**

> [INSERT SCREENSHOT: spp-validasi.png]

### Approve
- Pilih **Setujui Pengajuan**
- Tambah catatan (opsional)
- Klik **Proses Keputusan**
- SPP berpindah ke posisi approval berikutnya

### Revisi (kembali ke MAKER)
- Pilih **Revisi ke Maker**
- Isi catatan perbaikan
- MAKER akan mendapat notifikasi

### Tolak (terminal)
- Pilih **Tolak Pengajuan**
- WAJIB isi alasan penolakan
- SPP masuk status Rejected, tidak bisa diproses lagi

## 8.7 Pencairan Dana (KASIR_PUSAT)

1. Buka **Data SPP**
2. Filter SPP dengan status **Approved**, posisi **KASIR_PUSAT**
3. Klik tombol **Cairkan Dana**

> [INSERT SCREENSHOT: spp-cairkan.png]

4. Konfirmasi nominal pencairan
5. Klik **Ya, Cairkan**
6. Pastikan dana sudah ditransfer via e-banking SEBELUM konfirmasi

## 8.8 Cetak PDF

SPP dengan status **Approved** atau **Disbursed** bisa dicetak:
- **Preview** — lihat tampilan PDF sebelum cetak (modal)
- **Cetak** — download file PDF resmi

PDF berisi:
- Kop surat institusi
- No surat, tanggal, project
- Sumber dana & tujuan pembayaran
- Rincian anggaran per item + total
- Terbilang nominal
- Tanda tangan digital (jika sudah diupload)

## 8.9 Edit Profil

Klik avatar → **Edit Profil**.

> [INSERT SCREENSHOT: profile.png]

Fitur:
- **Upload Tanda Tangan** — upload PNG/JPG (max 2MB)
- **Ganti Password** — minimal 8 karakter, kombinasi huruf besar + kecil + angka
- Password lama harus benar untuk mengganti password baru

## 8.10 Notifikasi

Klik ikon **lonceng** di pojok kanan atas.

> [INSERT SCREENSHOT: notifikasi.png]

Jenis notifikasi:
- **NEW_SPP** — SPP baru perlu diproses
- **PENDING_APPROVAL** — SPP sudah di-approve, menunggu role berikutnya
- **REVISED** — SPP direvisi, perlu perbaikan
- **REJECTED** — SPP ditolak
- **DISBURSED** — SPP sudah dicairkan
