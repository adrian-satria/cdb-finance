# 04 — Workflow SPP (State Machine)

## 4.1 Alur Dasar

```
MAKER → [Initial Position] → ... → MANAGER_KEUANGAN
                                       ├─ (< 50jt) → KASIR_PUSAT → Disbursed
                                       └─ (≥ 50jt) → DIREKTUR → KASIR_PUSAT → Disbursed

Setiap tahap bisa:
  ├─ Approve → lanjut ke next position
  ├─ Revise → kembali ke MAKER
  └─ Reject → terminal (REJECTED)
```

## 4.2 Tiga Flow Type

### PROJECT_FLOW (Project 38, 40)

```
MAKER → AREA_MANAGER → FINANCE_PROJECT → PROJECT_MANAGER
     → MANAGER_KEUANGAN
         ├─ approve_under_threshold → KASIR_PUSAT → FINISH
         └─ approve_over_threshold → DIREKTUR → KASIR_PUSAT → FINISH
```

### PO_PK_TC_FLOW (Project 01, 03, 07)

```
MAKER → KOORDINATOR_KEUANGAN (01) / KOORDINATOR_PK (03) / KOORDINATOR_TC (07)
     → MANAGER_KEUANGAN
         ├─ approve_under_threshold → KASIR_PUSAT → FINISH
         └─ approve_over_threshold → DIREKTUR → KASIR_PUSAT → FINISH
```

### BATRA_KLINIK_DIKLAT_FLOW (Project 02, 04, 06)

```
MAKER → KOORDINATOR_BATRA (02) / KOORDINATOR_KLINIK (04) / KOORDINATOR_DIKLAT (06)
     → MANAGER_PKP → MANAGER_KEUANGAN
         ├─ approve_under_threshold → KASIR_PUSAT → FINISH
         └─ approve_over_threshold → DIREKTUR → KASIR_PUSAT → FINISH
```

## 4.3 Mapping Project → Initial Position

| Project | Flow Type | Posisi Awal |
|---------|-----------|-------------|
| 38 | PROJECT_FLOW | AREA_MANAGER |
| 40 | PROJECT_FLOW | AREA_MANAGER |
| 01 | PO_PK_TC_FLOW | KOORDINATOR_KEUANGAN |
| 03 | PO_PK_TC_FLOW | KOORDINATOR_PK |
| 07 | PO_PK_TC_FLOW | KOORDINATOR_TC |
| 02 | BATRA_KLINIK_DIKLAT_FLOW | KOORDINATOR_DIKLAT |
| 04 | BATRA_KLINIK_DIKLAT_FLOW | KOORDINATOR_KLINIK |
| 06 | BATRA_KLINIK_DIKLAT_FLOW | KOORDINATOR_BATRA |
| Lainnya | PROJECT_FLOW (default) | MANAGER_KEUANGAN |

## 4.4 Approval Threshold

- **Ambang batas:** Rp 50.000.000 (lima puluh juta)
- **Di bawah threshold:** MANAGER_KEUANGAN bisa approve langsung ke KASIR_PUSAT
- **Sama dengan atau di atas threshold:** harus melalui DIREKTUR
- Perbandingan menggunakan `bccomp()` (bcmath) untuk presisi

```php
// config/spp_workflow.php
'director_approval_threshold' => '50000000',
```

## 4.5 Common Actions (semua flow)

```php
'common_actions' => [
    'revise' => ['next' => 'MAKER', 'status' => 'Revisi'],
    'reject' => ['next' => 'REJECTED', 'status' => 'Rejected'],
],
```

## 4.6 Status Transitions

```
                 ┌──────────┐
                 │  Pending  │
                 └────┬─────┘
                      │
         ┌────────────┼────────────┐
         │            │            │
         ▼            ▼            ▼
     Revision     Approved    Rejected
         │            │            │
         │            ▼            │
         │      ┌─────┴─────┐      │
         │      │  < 50jt   │      │
         │      └─────┬─────┘      │
         │            │            │
         │            ▼            │
         │   ┌───────────────┐     │
         │   │Pending Director│     │
         │   │  Otorisasi    │     │
         │   └───────┬───────┘     │
         │           │             │
         │           ▼             │
         │      ┌────────┐         │
         └──────│Approved│         │
                └───┬────┘         │
                    │              │
                    ▼              │
              ┌──────────┐         │
              │ Disbursed│         │
              └──────────┘         │
                                   │
                                   ▼
                              ┌──────────┐
                              │ Rejected  │ (terminal)
                              └──────────┘
```

## 4.7 Idempotency & Race Condition

- **Pencairan:** `lockForUpdate()` pada `surat_permintaan` → cek status `Approved` + posisi `KASIR_PUSAT`
- **Budget:** `lockForUpdate()` pada `master_budget` sebelum increment `terserap`
- Jika duplikat request, hanya yang pertama lolos, sisanya throw exception

## 4.8 Flow Implementation

Config terpusat di `config/spp_workflow.php` → dibaca oleh `SppWorkflowService`.

```php
// Contoh: MANAGER_KEUANGAN approve dengan nominal 30jt (under threshold)
$result = $workflowService->getNextPosition(
    currentRole: 'MANAGER_KEUANGAN',
    kodeProject: '38',
    nominal: '30000000',
    action: 'approve'
);
// Result: ['next' => 'KASIR_PUSAT', 'status' => 'Approved']

// Nominal 60jt (over threshold)
$result = $workflowService->getNextPosition(
    currentRole: 'MANAGER_KEUANGAN',
    kodeProject: '38',
    nominal: '60000000',
    action: 'approve'
);
// Result: ['next' => 'DIREKTUR', 'status' => 'Pending Director Otorisasi']
```
