<?php

namespace App\Observers;

use App\Models\SuratPermintaan;
use App\Services\AuditLogService;

class SppObserver
{
    /**
     * Handle the SuratPermintaan "created" event.
     */
    public function created(SuratPermintaan $suratPermintaan): void
    {
        AuditLogService::log(
            'INSERT_SPP',
            "SPP baru dibuat: No {$suratPermintaan->no_surat}",
            null
        );
    }

    /**
     * Handle the SuratPermintaan "updated" event.
     */
    public function updated(SuratPermintaan $suratPermintaan): void
    {
        $changes = $suratPermintaan->getChanges();
        $original = $suratPermintaan->getOriginal();

        // Track status changes
        if (isset($changes['status_surat']) && isset($original['status_surat'])) {
            AuditLogService::logSppHistory(
                $suratPermintaan->no_surat,
                $original['status_surat'],
                $changes['status_surat'],
                $original['posisi_saat_ini'] ?? null,
                $changes['posisi_saat_ini'] ?? $original['posisi_saat_ini'],
                "Status berubah: {$original['status_surat']} -> {$changes['status_surat']}",
                $original
            );
        }

        // Track position changes
        if (isset($changes['posisi_saat_ini']) && isset($original['posisi_saat_ini'])) {
            AuditLogService::log(
                'WORKFLOW_TRANSITION',
                "SPP {$suratPermintaan->no_surat} pindah: {$original['posisi_saat_ini']} -> {$changes['posisi_saat_ini']}",
                $original
            );
        }

        AuditLogService::log(
            'UPDATE_SPP',
            "SPP {$suratPermintaan->no_surat} diperbarui",
            $original
        );
    }

    /**
     * Handle the SuratPermintaan "deleted" event.
     */
    public function deleted(SuratPermintaan $suratPermintaan): void
    {
        AuditLogService::log(
            'DELETE_SPP',
            "SPP {$suratPermintaan->no_surat} dihapus dari sistem",
            $suratPermintaan->getOriginal()
        );
    }
}
