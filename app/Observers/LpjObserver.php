<?php

namespace App\Observers;

use App\Models\LpjUangMuka;
use App\Services\AuditLogService;

class LpjObserver
{
    public function created(LpjUangMuka $lpj): void
    {
        AuditLogService::log('INSERT_LPJ', "LPJ baru dibuat: No {$lpj->no_lpj}", null);
    }

    public function updated(LpjUangMuka $lpj): void
    {
        $changes = $lpj->getChanges();
        $original = $lpj->getOriginal();

        if (isset($changes['status_lpj']) && isset($original['status_lpj'])) {
            AuditLogService::log('LPJ_STATUS_CHANGE', "LPJ {$lpj->no_lpj} status: {$original['status_lpj']} -> {$changes['status_lpj']}", $original);
        }
        if (isset($changes['posisi_saat_ini']) && isset($original['posisi_saat_ini'])) {
            AuditLogService::log('LPJ_WORKFLOW_TRANSITION', "LPJ {$lpj->no_lpj} pindah: {$original['posisi_saat_ini']} -> {$changes['posisi_saat_ini']}", $original);
        }

        $relevant = ['status_lpj', 'posisi_saat_ini', 'total_realisasi', 'selisih'];
        if (array_intersect_key($changes, array_flip($relevant))) {
            AuditLogService::log('UPDATE_LPJ', "LPJ {$lpj->no_lpj} diperbarui", $original);
        }
    }

    public function deleted(LpjUangMuka $lpj): void
    {
        AuditLogService::log('DELETE_LPJ', "LPJ {$lpj->no_lpj} dihapus", $lpj->getOriginal());
    }
}
