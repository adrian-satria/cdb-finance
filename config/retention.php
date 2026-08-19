<?php

use Illuminate\Support\Facades\Schema;

/*
 * Masa retensi data (dalam hari) sebelum dipindah ke cold storage (arsip).
 * Standar keuangan Indonesia: dokumen perpajakan & pertanggungjawaban = 10 tahun,
 * audit trail = 5 tahun (masa pemeriksaan pajak UU KUP), activity log = 180 hari.
 */
return [
    // Transaksi inti & bukti - wajib 10 tahun
    'surat_permintaan' => 10 * 365,
    'surat_permintaan_detail' => 10 * 365,
    'surat_permintaan_files' => 10 * 365,
    'pengajuan_uang_muka' => 10 * 365,
    'pengajuan_uang_muka_detail' => 10 * 365,
    'pengajuan_uang_muka_files' => 10 * 365,
    'lpj_uang_muka' => 10 * 365,
    'lpj_uang_muka_detail' => 10 * 365,
    'lpj_uang_muka_files' => 10 * 365,
    'reimburse_lpj' => 10 * 365,
    'reimburse_lpj_files' => 10 * 365,
    'spp_history' => 10 * 365,

    // Audit trail - 5 tahun
    'audit_trails' => 5 * 365,

    // Activity log - 180 hari
    'activity_logs' => 180,

    // Tabel yang sengaja dikecualikan (jangan diarsip):
    // users, user_access, areas, projects, master_budget, budget_area,
    // notifications, system_settings, cache, jobs, spp_sequences, document_sequences
];
