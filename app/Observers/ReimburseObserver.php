<?php

namespace App\Observers;

use App\Models\ReimburseLpj;
use App\Services\AuditLogService;

class ReimburseObserver
{
    public function created(ReimburseLpj $reimburse): void
    {
        AuditLogService::log('INSERT_REIMBURSE', "Reimburse baru dibuat: No {$reimburse->no_reimburse}", null);
    }

    public function updated(ReimburseLpj $reimburse): void
    {
        $changes = $reimburse->getChanges();
        $original = $reimburse->getOriginal();

        if (isset($changes['status_reimburse']) && isset($original['status_reimburse'])) {
            AuditLogService::log('REIMBURSE_STATUS_CHANGE', "Reimburse {$reimburse->no_reimburse} status: {$original['status_reimburse']} -> {$changes['status_reimburse']}", $original);
        }
        if (isset($changes['posisi_saat_ini']) && isset($original['posisi_saat_ini'])) {
            AuditLogService::log('REIMBURSE_WORKFLOW_TRANSITION', "Reimburse {$reimburse->no_reimburse} pindah: {$original['posisi_saat_ini']} -> {$changes['posisi_saat_ini']}", $original);
        }

        $relevant = ['status_reimburse', 'posisi_saat_ini', 'total_nominal'];
        if (array_intersect_key($changes, array_flip($relevant))) {
            AuditLogService::log('UPDATE_REIMBURSE', "Reimburse {$reimburse->no_reimburse} diperbarui", $original);
        }
    }

    public function deleted(ReimburseLpj $reimburse): void
    {
        AuditLogService::log('DELETE_REIMBURSE', "Reimburse {$reimburse->no_reimburse} dihapus", $reimburse->getOriginal());
    }
}
