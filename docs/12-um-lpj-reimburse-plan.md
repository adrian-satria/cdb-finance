# 12 — Planning Modul Uang Muka (UM), LPJ, dan Reimburse

> Status: **Direncanakan — belum diimplementasikan**
> Tanggal: 2026-08-04
> Sumber: diskusi dengan divisi keuangan

---

## 1. Alur Bisnis

### 1.1 Pengajuan Uang Muka (UM)
```
Input → Approve → Cair → Menjadi Piutang (masuk Register UM)
```
- Semua staf/karyawan bisa mengajukan UM (maker = seluruh staf, bukan hanya role MAKER)
- User minta dana duluan untuk kegiatan
- **Budget TIDAK dipotong saat UM cair** — hanya di-cek (ceiling check)
- Setelah cair → status "Belum LPJ" (hutang pertanggungjawaban)
- Masuk Register UM

### 1.2 LPJ Uang Muka
```
Input Realisasi → Approve → Hitung Selisih
```
- User lampirkan bukti penggunaan advance
- `Sisa = UangMuka - Realisasi`
- Jika `sisa > 0` → wajib setor balik (refund) — kasir hitung, staf transfer ke rekening asal
- Jika `sisa < 0` → otomatis buka Reimburse sebesar selisih
- **Budget DIPOTONG saat LPJ disetujui** (realisasi)

### 1.3 Reimburse LPJ
```
Sisa Negatif → Approve → Cair
```
- Hanya timbul jika realisasi > uang muka
- Cair dari budget lagi (potong budget saat cair)
- Approval sementara: Finance → Kasir (workflow **editable** lewat config)

---

## 2. Aturan Kunci

| Aturan | Detail |
|--------|--------|
| **Budget potong** | Saat LPJ disetujui (bukan saat UM cair) |
| **Maker UM** | Semua staf, bukan hanya role MAKER |
| **Batas LPJ** | 14 hari setelah cair. Ada warning. Jika overdue & belum LPJ → **tidak bisa ajukan UM baru** |
| **Reimburse** | Auto-create saat LPJ disetujui & realisasi > UM. Approval Finance → Kasir |
| **Refund** | Kasir hitung, staf transfer balik ke rekening asal. Dicatat sebagai piutang |
| **Register UM** | Halaman khusus daftar UM outstanding (belum LPJ) |
| **Workflow UM** | Sama seperti SPP (per project, sesuai nominal) |
| **Workflow LPJ** | 2-3 level, tanpa kasir |
| **Workflow Reimburse** | Editable via config, bukan hardcode |

---

## 3. Skema Database (8 Tabel Baru)

| Tabel | Relasi | Kolom Kunci |
|-------|--------|-------------|
| `pengajuan_uang_muka` | - | `no_aju` (PK string), `id_pengaju`, `kode_project`, `kode_area`, `total_nominal`, `sisa_lpj` (tracking piutang), `status_um`, `posisi_saat_ini`, `tanggal_jatuh_tempo` |
| `pengajuan_uang_muka_detail` | FK `no_aju` | Rincian budget |
| `pengajuan_uang_muka_files` | FK `no_aju` | File lampiran |
| `lpj_uang_muka` | FK `no_aju` | Link ke advance, `total_realisasi`, `selisih`, `status_lpj`, `posisi_saat_ini` |
| `lpj_uang_muka_detail` | FK `no_lpj` | Realisasi per item |
| `lpj_uang_muka_files` | FK `no_lpj` | Bukti/kuitansi |
| `reimburse_lpj` | FK `no_lpj` | Hanya jika realisasi > advance, `total_nominal`, `status_reimburse`, `posisi_saat_ini` |
| `reimburse_lpj_files` | FK `no_reimburse` | Lampiran |

---

## 4. Komponen Reuse (Tidak Perlu Tulis Ulang)

| Komponen | Keterangan |
|----------|------------|
| `FileUploadService` | Lampiran, tanda tangan — tinggal panggil |
| `NotificationService` | Notifikasi tiap workflow |
| `AuditLogService` | Logging + history |
| `BudgetValidationService::lockAndValidateMultiple` | Validasi ceiling. **Dipanggil saat LPJ approved**, bukan saat UM cair |
| `RoleHelper` | Role untuk advance & LPJ sudah tercakup |
| `SppObserver pattern` | Buat `AdvanceObserver`, `LpjObserver` dengan pola sama |
| `SppNumberGeneratorService` | Reuse pattern untuk generate `no_aju`, `no_lpj`, `no_reimburse` |

---

## 5. Struktur File Baru

| Kategori | Jumlah | Detail |
|----------|--------|--------|
| Migration | 8 | Tabel + indexes |
| Model | 8 | Advance, AdvanceDetail, AdvanceFiles, Lpj, LpjDetail, LpjFiles, Reimburse, ReimburseFiles |
| Controller | 3 | `AdvanceController`, `LpjController`, `ReimburseController` (1 controller per modul) |
| Workflow Config | 3 | `config/um_workflow.php`, `config/lpj_workflow.php`, `config/reimburse_workflow.php` |
| Workflow Service | 3 | `AdvanceWorkflowService`, `LpjWorkflowService`, `ReimburseWorkflowService` |
| FormRequest | ~6 | Store + Validate per modul |
| View Blade | ~12 | index, form, detail, pdf, modals per modul |
| Observer | 3 | AdvanceObserver, LpjObserver, ReimburseObserver |
| Route | ~20 baris | Menu Uang Muka: pengajuan UM, LPJ UM, Reimburse UM |
| **Total** | **~45-50 file** | ~3500-4500 baris kode |

**Rekomendasi teknis:**
1. 1 Controller per modul (bukan 1 controller super besar seperti SPP) — lebih mudah maintenance
2. `AdvanceWorkflowService` dan `LpjWorkflowService` terpisah — logika transisi berbeda
3. Gunakan trait untuk fungsi reuse (file upload, notification) supaya tidak duplikasi
4. Mantapkan dulu flow advance penuh (termasuk cancel & revisi), baru LPJ & reimburse — LPJ bergantung status advance

---

## 6. Prioritas Eksekusi

```
Fase 1: Migration + Model (semua tabel)
  → Fase 2: Config workflow (um, lpj, reimburse)
    → Fase 3: AdvanceController + views + register
      → Fase 4: LpjController + views
        → Fase 5: ReimburseController + views (auto-create dari LPJ)
          → Fase 6: Observers + Notifications
            → Fase 7: Testing end-to-end (UM → LPJ → Reimburse/Refund)
```

**Estimasi: ~8-10 jam kerja** (tergantung kerumitan view).
