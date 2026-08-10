# Review Sistem CDB Finance (B-SMART) — Temuan & Rekomendasi

Tanggal review: 2026-08-06
Scope: struktur file, flow approval SPP (3 alur), budget, otorisasi, keamanan, audit trail.

---

## Ringkasan Arsitektur

- **Stack:** Laravel 11 + Bootstrap, DB relasional (users, user_access, surat_permintaan, surat_permintaan_detail, budget_area, master_budget, project_area, spp_history, audit_trails, notifications).
- **3 Alur Approval** (diatur di `config/spp_workflow.php`, dimodelkan state machine di `app/Services/SppWorkflowService.php`):
  1. **PROJECT_FLOW** (project 38, 40): AREA_MANAGER → FINANCE_PROJECT → PROJECT_MANAGER → MANAGER_KEUANGAN → (DIREKTUR jika >50jt) → KASIR_PUSAT → FINISH.
  2. **PO_PK_TC_FLOW** (project 01, 03, 07): KOORDINATOR_* → MANAGER_KEUANGAN → (DIREKTUR) → KASIR_PUSAT → FINISH.
  3. **BATRA_KLINIK_DIKLAT_FLOW** (project 02, 04, 06): KOORDINATOR_* → MANAGER_PKP → MANAGER_KEUANGAN → (DIREKTUR) → KASIR_PUSAT → FINISH.
- Aksi utama: `SppController@store` (input maker), `@validasi` (approve/revise/reject), `@cairkan` (disbursement, idempotent).
- Budget dikunci per area di `budget_area` (alokasi_dana, terserap); `master_budget` sebagai master; sinkronisasi `terserap` saat pencairan.
- Audit trail otomatis via `SppObserver` + `AuditLogService`.

---

## BUG KRITIS / KEAMANAN (prioritas tertinggi)

### B1. Role Escalation — Pencairan (`cairkan`) tanpa cek role aktual
`SppController@cairkan` hanya memvalidasi status `Approved` + `posisi_saat_ini = KASIR_PUSAT`, **tidak pernah memeriksa `session('role')` dari user yang mengeksekusi**. Satu-satunya penjaga adalah `DisburseSppRequest::authorize()` yang mengecek `RoleHelper::isGlobal(session('role'))` — dan session role adalah data yang bisa dipengaruhi lewat `setPeran`/`switchRole`.

Namun yang lebih parah: `DisburseSppRequest::authorize()` hanya mengembalikan false → Laravel return 403. Tapi ini adalah **satu-satunya gerbang**. Tidak ada pengecekan bahwa user sungguh adalah KASIR_PUSAT yang ditugaskan. **Semua role global** (ADMIN, DIREKTUR, KASIR_PUSAT) bisa mencairkan. Bila ADMIN atau DIREKTUR seharusnya tidak boleh eksekusi pencairan, ini bug otorisasi.
- **Risiko:** MAKER/role non-global bisa kirim POST `/spp/cairkan` → dicegat authorize() → aman. Namun boundary global→kasir tidak ditegakkan.
- **Rekomendasi:** cek `session('role') === 'KASIR_PUSAT'` (atau role khusus pencairan) di controller, bukan hanya `isGlobal`. Log siapa mencairkan.

### B2. ADMIN auto-approve seluruh chain
`SppWorkflowService::validateWorkflowTransition`: jika role = `ADMIN`, langsung `return true`. Konsekuensi: ADMIN yang login memilih peran ADMIN bisa **mengesahkan sendiri** SPP di posisi mana pun, termasuk DIREKTUR threshold >50jt, lalu mencairkan — seluruh siklus kontrol internal terlewati.
- **Rekomendasi:** batasi ADMIN hanya sebagai auditor/viewer. Jika perlu tindakan atas nama, wajib require password/2FA ulang atau catat log `ADMIN_IMPERSONATE`.

### B3. Budget tidak "di-reserve" saat SPP dibuat → double-booking
`BudgetValidationService::lockAndValidateMultiple` hanya **menghitung** sisa saldo saat `store`, tetapi `terserap` baru di-increment saat **pencairan**. Dua SPP yang sama-sama Pending dengan budget sama bisa lolos keduanya; saat keduanya dicairkan, yang kedua over-commit.
- Contoh: pagu 100jt. SPP A (90jt) Pending, SPP B (90jt) Pending — keduanya lolos validasi store. Setelah A cair, terserap 90jt, B cair → total 180jt, sisa -80jt.
- **Rekomendasi:** terapkan *budget reservation*: buat kolom `terserap_sementara` / status `reserved` saat SPP dibuat; atau hitung `alokasi - terserap - SUM(SPP Pending belum cair)` saat validasi store.

### B4. Overspend hanya warning, bukan blocking
Sistem **mengizinkan** SPP melebihi pagu (isValid=true, isOverBudget=true) dan hanya menampilkan warning "PERHATIAN: melebihi pagu". Untuk sistem keuangan ini adalah kebijakan berisiko tinggi: defisit aktual diterima tanpa persetujuan eksplisit.
- **Rekomendasi:** buat mekanisme approval khusus overspend (mis. wajib persetujuan DIREKTUR/MANAGER_KEUANGAN) atau jadikan blocking dengan alur pengecualian yang terdokumentasi.

### B5. Nomor SPP collision & race condition
`SppNumberGeneratorService@generateNextNumber`:
- `substr(no_surat, -3) + 1` — begitu urutan >999, nomor jadi 4 digit → `substr(-3)` ambil angka salah, collision besar.
- Sequence dicari via `whereYear('created_at') orderBy desc` tanpa filter bulan/urutan, di dalam transaksi dgn `lockForUpdate` tapi tanpa `where` unik — dua request simultan bisa dapat nomor sama. `checkCollision` bisa throw tapi race window ada.
- Format `YYYY/Romawi/SPP/{project}/NNN` memakai `created_at` (jam server), padahal `tanggal` pengajuan bisa beda tahun/bulan → nomor tidak sesuai bulan surat.
- **Rekomendasi:** gunakan sequence table (`spp_sequences`) dgn unique key `(tahun, bulan, kode_project)`, atomic increment `UPDATE ... RETURNING`, atau DB auto-increment + nomor tampilan terpisah. Tambah unique index `no_surat`.

### B6. Session role tidak diverifikasi area/project scoping
`ValidateSession` hanya memeriksa `role` ada di `user_access`. Area & project yang tersimpan di session TIDAK pernah diverifikasi ulang di banyak controller. `switchRole`/`setPeran` memvalidasi keberadaan, tapi tidak ada pengecekan konsisten bahwa akses area/project user sesuai SPP target di setiap endpoint.
- `RoleHelper::canAccessSpp` dipakai di `getDetailItems`, `downloadFile`, `cetakPdf`, `previewPdf` — bagus. Tapi `validasi` dan `cairkan` TIDAK memakai `canAccessSpp`: hanya state machine. Seorang KOORDINATOR_KEUANGAN project X bisa memvalidasi SPP project Y (asalkan posisi_saat_ini cocok) — karena query hanya `where no_surat`, tanpa filter area/project scope.
- **Rekomendasi:** di `validasi` dan `cairkan`, tambah cek `RoleHelper::canAccessSpp(...)` sebelum eksekusi.

### B7. `cairkan` — biaya_admin dibebankan ke seluruh item tanpa validasi budget
`updateTerserap` menambahkan `biaya_admin` ke **setiap** item (`bcadd(nominal, biayaAdmin)` per item). Satu SPP dengan 5 item → biaya admin terhitung 5x. Di sisi lain, `total_dibayar` di notifikasi hanya `total_nominal + biaya_admin` (sekali).
- **Rekomendasi:** hitung biaya admin ke salah satu item (atau alokasikan proporsional), bukan semua item.

### B8. Notifikasi `sendToRole` tidak memfilter area/project
`NotificationService::sendToRole` mengirim ke **semua user** dengan role tersebut, apa pun area/project-nya. Akibat: KOORDINATOR project 01 menerima notifikasi SPP project 07; MAKER area A dapat notif SPP area B. Dua-duanya harusnya pakai `sendToProjectRoles`/`sendToAreaRoles`.
- **Rekomendasi:** di `store` dan `validasi`, pilih channel notifikasi berdasarkan scoping posisi target (role global vs role ber-lingkup).

---

## BUG FUNGSIONAL / KONSISTENSI

### F1. Threshold DIREKTUR dibandingkan dengan `total_nominal` seluruh item
`SppWorkflowService@getNextPosition` membandingkan `$surat->total_nominal` (total seluruh item) dengan threshold 50jt. Untuk SPP multi-budget, keputusan "perlu DIREKTUR atau tidak" seharusnya per budget line (atau sesuai kebijakan). Saat ini satu item 40jt + satu item 20jt = total 60jt → masuk jalur DIREKTUR walau tak ada item di atas 50jt. Konfirmasi kebijakan.

### F2. MAKER bisa mengajukan SPP untuk project/area di luar scope-nya
`StoreSppRequest` tidak membatasi `kode_project`/`kode_area`. Seorang MAKER area PUSAT bisa set `kode_project=40` milik area lain. `SppController@create` sudah membatasi pilihan di UI (area locked), tapi `store` tidak memvalidasi ulang server-side.
- **Rekomendasi:** tambah validasi `kode_project` ∈ project_area milik `session('kode_area')`, dan `kode_area` = `session('kode_area')` untuk role scoped.

### F3. SPP Revisi tidak punya alur edit
Setelah direvisi (`posisi=MAKER`, status `Revisi`), tidak ada form edit untuk mengubah item/lampiran. `index` menampilkan SPP revisi tapi tak ada aksi "edit". Alur macet: maker tidak bisa perbaiki.
- Cek: apakah tombol "Periksa" untuk MAKER yang menampilkan form validasi? MAKER tidak di posisi `MAKER` dalam state machine untuk aksi validasi — state machine `validateWorkflowTransition` menolak. Sehingga SPP Revisi **stuck**.
- **Rekomendasi:** sediakan endpoint edit/resubmit untuk status Revisi, atau set `posisi_saat_ini` kembali ke posisi awal setelah revisi diperbaiki.

### F4. `SppObserver@updated` — UPDATE_SPP dicatat tanpa pandang bulu
Setiap update (termasuk touch-only / perubahan `updated_at`) menulis log `UPDATE_SPP` + payload_before. Untuk SPP besar, payload_before bisa berisi seluruh kolom termasuk data rekening. Volume log tinggi & berisiko bocor data sensitif di audit trail.
- **Rekomendasi:** filter perubahan kolom yang relevan (status, posisi, nominal) dan batasi payload.

### F5. Tidak ada "edit" di kelola/index untuk SPP
Sistem tidak menyediakan koreksi data transaksi (bank tujuan, nomor rekening, keterangan). Bila ada salah input, satu-satunya jalan reject → buat ulang. Kurang ergonomis untuk admin.

### F6. `index` / `kelola` menampilkan `files_maker`/`files_checker` hanya dari halaman saat itu
Query file menggunakan `whereIn('no_surat', $sppNumbers)` per halaman — OK untuk halaman kecil, tapi N+1 dihindari. Tidak ada masalah performa besar. (Kategori CHECKER di-upload oleh checker yang merupakan role posisi terakhir; label "MANAGEMENT" di UI tidak selalu sesuai — detail UI.)

---

## KEAMANAN / HARDENING

### S1. Rate-limit login hanya throttle:5,1 (IP-based, mudah bypass)
`Route::post('/login')` diberi `throttle:5,1`. Rate limiter default Laravel berbasis IP — attacker dapat memakai proxy/IP berganti. Belum ada lockout per-username / monitoring brute force berkelanjutan.
- **Rekomendasi:** tambah lockout berbasis username + log percobaan gagal (`logLogin` ada tapi belum dipanggil di `authenticate`!). Perhatikan: `AuditLogService::logLogin` tidak pernah dipanggil dari `AuthController::authenticate` — login gagal/sukses **tidak tercatat**.

### S2. Tidak ada Forgot/Reset Password
Tidak ada route reset password. Pengguna lupa password → harus admin reset manual. (user reset password via `ProfileController@changePassword` ada tapi tidak ada self-service reset.)

### S3. `SystemSetting` tidak digunakan
Settings (session_timeout, max_file_size, maintenance_mode, dll.) bisa di-edit admin tapi TIDAK ada yang membacanya di runtime:
- `ValidateSession` timeout hardcoded 7200 detik (2 jam), bukan dari settings.
- `StoreSppRequest` max file hardcoded 5120 KB, bukan dari settings.
- `maintenance_mode` tidak pernah dicek.
- **Rekomendasi:** konsumsi settings di middleware/controller, atau hapus modul agar tidak menyesatkan.

### S4. `ProfileController@uploadSignature` & `changePassword` tidak pakai FormRequest & validasi longgar
- `uploadSignature` memakai `mimes` dari Laravel (berbasis extension + MIME) — relatif aman, tapi `getClientOriginalExtension` bisa di-spoof; pakai `isValidMime` dari FileUploadService (finfo) untuk konsistensi.
- `changePassword` tidak memaksa logout sesi lain. Gunakan `Auth::logoutOtherDevices` (butuh AuthenticateSession middleware).

### S5. `downloadFile` — parameter path aman tapi ownership lemah untuk role global
`basename()` dipakai — aman dari traversal. Namun `canAccessSpp` memberikan akses penuh ke `ADMIN`, `KASIR_PUSAT`, `DIREKTUR`, `MANAGER_KEUANGAN` tanpa cek kepemilikan. Untuk arsip keuangan sensitif, pertimbangkan log akses file & batasan role.

### S6. `cetakPdf` — parameter query `no_surat` tidak divalidasi format
`$request->query('no_surat')` langsung dipakai. Meski DB aman (parameter binding), input tidak divalidasi → redirect error page ketika kosong. Sama di `previewPdf`.

### S7. CSRF: semua POST memakai `@csrf` (terlihat di view) — OK. Tapi `setPeran` dan `switchRole` adalah POST tanpa throttle → brute force role? (validasi ownership sudah ada, risiko kecil.)

### S8. `UserController@store` — `id_user` di-set dari `id` (auto increment) — konsisten. Namun `UserAccessController@update` menghapus semua akses lalu insert ulang dalam satu transaksi — jika salah satu insert gagal, transaksi rollback (aman). Tidak ada unique constraint di `user_access(id_user, role, kode_area, kode_project)` → duplikat akses bisa terbentuk dari double-click.

---

## IMPROVEMENT / UX

### I1. Laporan & monitoring
- Tidak ada filter bulan untuk laporan budget_vs_actual (hanya tahun).
- Tidak ada export SPP list (hanya reports admin).
- Tidak ada metrik SLA / waktu approval per tahap (bisa dihitung dari spp_history).

### I2. Konfirmasi destruktif & audit
- `BudgetController@destroy`, `UserController@destroy`, `AreaController@destroy`, `ProjectController@destroy` tidak menulis audit log khusus (hanya default Laravel). Sebaiknya log siapa menghapus apa, + payload.

### I3. Soft-delete / riwayat
- Menghapus user/area/project yang pernah terlibat transaksi → FK integrity bermasalah. `id_maker` di surat_permintaan bisa menunjuk user terhapus → PDF tanda tangan `nama_lengkap` jadi "-" (fallback `$user ? ... : '-'` sudah menangani, tapi data hilang). Pertimbangkan soft-delete untuk user.

### I4. Validasi input lebih ketat
- `StoreSppRequest::items.*.kode_budget` hanya `required|string` — tidak memverifikasi budget exists untuk area/project ybs (dicek nanti di `lockAndValidateMultiple`, tapi error saat store = UX buruk). Tambahkan aturan custom atau validasi dini.
- `no_rekening_tujuan`, `bank_tujuan` tidak divalidasi format.

### I5. Konsistensi mata uang
- `total_nominal` decimal(15,2); `items.jumlah` numeric max 999999999999.99. Hitung `totalNominal` memakai bcmath di backend tapi `number_format` di banyak tempat (rounding display saja — aman). Namun `cairkan` `biaya_admin` dari string user tanpa sanitasi `bcmath` sebelum disimpan — sudah dipakai bcadd, aman.

### I6. Notifikasi referensi link
- `fetchUnread` mengarahkan semua notif SPP ke `/spp` (list umum), bukan detail SPP. Improve: link ke `/spp?no_surat=...` atau detail modal.

### I7. Cache
- `Cache::remember('ref_projects', 3600)` dan `ref_areas` — bagus. Tapi update project/area tidak `Cache::forget` → data stale hingga 1 jam. Tambah forget saat create/update/delete.

### I8. Pengujian
- Ada folder `tests/` — cek isi & tambahkan test untuk state machine (approve path, threshold, reject/revise, idempotency cairkan). Ini fondasi paling penting untuk mencegah regresi pada alur approval.

---

## Prioritas Perbaikan

| Prioritas | Temuan | Tindakan | Status |
|---|---|---|---|
| P0 | B1 role escalation cairkan | cek role eksplisit KASIR_PUSAT | ✅ FIXED (branch `fix/p0-security-critical`) |
| P0 | B2 admin auto-approve | batasi peran ADMIN | ✅ FIXED (ADMIN read-only) |
| P0 | B3 budget double-booking | reservation / hitung pending | ✅ FIXED (`terserap_sementara`) |
| P0 | B5 nomor SPP collision | sequence table + unique index | ✅ FIXED (`spp_sequences` atomic) |
| P1 | B4 overspend blocking | kebijakan approval khusus | ⏳ belum |
| P1 | B6 scoping di validasi/cairkan | tambah canAccessSpp | ✅ FIXED (canAccessSpp di validasi) |
| P1 | B7 biaya_admin 5x | alokasikan sekali | ✅ FIXED (applied once on first item) |
| P1 | S1 login tidak di-log | panggil logLogin | ✅ FIXED (logLogin sukses/gagal + logLogout) |
| P1 | F3 SPP revisi stuck | endpoint edit/resubmit | ✅ FIXED (edit.blade.php + update flow) |
| P2 | B8, F1, F2, F4, S3, I1–I8 | perbaikan bertahap | ⏳ belum |

---
*Dokumen ini dibuat otomatis dari hasil review kode. Verifikasi tiap klaim dengan pembacaan kode terkait sebelum eksekusi perubahan.*
