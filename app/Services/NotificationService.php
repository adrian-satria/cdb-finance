<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Support\RoleHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendToRole($role, $type, $title, $message, $referenceType = null, $referenceId = null): void
    {
        $userIds = User::whereHas('akses', function ($q) use ($role) {
            $q->where('role', $role);
        })->pluck('id_user');

        $this->batchCreate($userIds, $type, $title, $message, $referenceType, $referenceId);
    }

    public function sendToUser($userId, $type, $title, $message, $referenceType = null, $referenceId = null)
    {
        return $this->create($userId, $type, $title, $message, $referenceType, $referenceId);
    }

    public function sendToRoleScoped($role, $kodeArea, $kodeProject, $type, $title, $message, $referenceType = null, $referenceId = null): void
    {
        if (RoleHelper::isStaffArea($role)) {
            $this->sendToAreaRoles($kodeArea, $type, $title, $message, $referenceType, $referenceId);

            return;
        }

        if (RoleHelper::isProjectScoped($role)) {
            $this->sendToProjectRoles($kodeProject, $type, $title, $message, $referenceType, $referenceId);

            return;
        }

        $this->sendToRole($role, $type, $title, $message, $referenceType, $referenceId);
    }

    public function sendToProjectRoles($kodeProject, $type, $title, $message, $referenceType = null, $referenceId = null): void
    {
        $roles = RoleHelper::PROJECT_FINANCE_ROLES;
        $userIds = User::whereHas('akses', function ($q) use ($roles, $kodeProject) {
            $q->whereIn('role', $roles)->where('kode_project', $kodeProject);
        })->pluck('id_user');

        $this->batchCreate($userIds, $type, $title, $message, $referenceType, $referenceId);
    }

    public function sendToAreaRoles($kodeArea, $type, $title, $message, $referenceType = null, $referenceId = null): void
    {
        $roles = RoleHelper::STAFF_AREA_ROLES;
        $userIds = User::whereHas('akses', function ($q) use ($roles, $kodeArea) {
            $q->whereIn('role', $roles)->where('kode_area', $kodeArea);
        })->pluck('id_user');

        $this->batchCreate($userIds, $type, $title, $message, $referenceType, $referenceId);
    }

    public function sendToAllAdmins($type, $title, $message, $referenceType = null, $referenceId = null): void
    {
        $this->sendToRole('ADMIN', $type, $title, $message, $referenceType, $referenceId);
    }

    private function create($userId, $type, $title, $message, $referenceType, $referenceId)
    {
        try {
            return Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal kirim notifikasi: '.$e->getMessage());

            return null;
        }
    }

    private function batchCreate($userIds, $type, $title, $message, $referenceType, $referenceId): void
    {
        if ($userIds->isEmpty()) {
            return;
        }

        $now = now();
        $records = $userIds->map(fn ($userId) => [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'is_read' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        try {
            DB::table('notifications')->insert($records);
        } catch (\Exception $e) {
            Log::error('Gagal batch kirim notifikasi: '.$e->getMessage());
        }
    }

    const TYPE_PENDING_APPROVAL = 'pending_approval';

    const TYPE_APPROVED = 'approved';

    const TYPE_REVISED = 'revised';

    const TYPE_REJECTED = 'rejected';

    const TYPE_DISBURSED = 'disbursed';

    const TYPE_NEW_SPP = 'new_spp';

    const TYPE_SYSTEM = 'system';
}
