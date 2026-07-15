<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public static function sendToRole($role, $type, $title, $message, $referenceType = null, $referenceId = null)
    {
        $users = User::whereHas('akses', function ($q) use ($role) {
            $q->where('role', $role);
        })->get();

        foreach ($users as $user) {
            self::create($user->id_user, $type, $title, $message, $referenceType, $referenceId);
        }
    }

    public static function sendToUser($userId, $type, $title, $message, $referenceType = null, $referenceId = null)
    {
        return self::create($userId, $type, $title, $message, $referenceType, $referenceId);
    }

    public static function sendToProjectRoles($kodeProject, $type, $title, $message, $referenceType = null, $referenceId = null)
    {
        $roles = ['FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN'];
        $users = User::whereHas('akses', function ($q) use ($roles, $kodeProject) {
            $q->whereIn('role', $roles)->where('kode_project', $kodeProject);
        })->get();

        foreach ($users as $user) {
            self::create($user->id_user, $type, $title, $message, $referenceType, $referenceId);
        }
    }

    public static function sendToAreaRoles($kodeArea, $type, $title, $message, $referenceType = null, $referenceId = null)
    {
        $roles = ['AREA_MANAGER', 'MAKER'];
        $users = User::whereHas('akses', function ($q) use ($roles, $kodeArea) {
            $q->whereIn('role', $roles)->where('kode_area', $kodeArea);
        })->get();

        foreach ($users as $user) {
            self::create($user->id_user, $type, $title, $message, $referenceType, $referenceId);
        }
    }

    public static function sendToAllAdmins($type, $title, $message, $referenceType = null, $referenceId = null)
    {
        self::sendToRole('ADMIN', $type, $title, $message, $referenceType, $referenceId);
    }

    private static function create($userId, $type, $title, $message, $referenceType, $referenceId)
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
            Log::error('Gagal kirim notifikasi: ' . $e->getMessage());
            return null;
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
