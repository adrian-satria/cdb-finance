<?php

namespace App\Observers;

use App\Models\PengajuanUangMuka;
use App\Services\AuditLogService;

class AdvanceObserver
{
    public function created(PengajuanUangMuka $um): void
    {
        AuditLogService::log('INSERT_UM', "UM baru dibuat: No {$um->no_aju}", null);
    }

    public function updated(PengajuanUangMuka $um): void
    {
        $changes = $um->getChanges();
        $original = $um->getOriginal();

        if (isset($changes['status_um']) && isset($original['status_um'])) {
            AuditLogService::log(
                'UM_STATUS_CHANGE',
                "UM {$um->no_aju} status: {$original['status_um']} -> {$changes['status_um']}",
                $original
            );
        }

        if (isset($changes['posisi_saat_ini']) && isset($original['posisi_saat_ini'])) {
            AuditLogService::log(
                'UM_WORKFLOW_TRANSITION',
                "UM {$um->no_aju} pindah: {$original['posisi_saat_ini']} -> {$changes['posisi_saat_ini']}",
                $original
            );
        }

        $relevant = ['status_um', 'posisi_saat_ini', 'sisa_lpj', 'tanggal_jatuh_tempo'];
        if (array_intersect_key($changes, array_flip($relevant))) {
            AuditLogService::log('UPDATE_UM', "UM {$um->no_aju} diperbarui", $original);
        }
    }

    public function deleted(PengajuanUangMuka $um): void
    {
        AuditLogService::log('DELETE_UM', "UM {$um->no_aju} dihapus", $um->getOriginal());
    }
}
