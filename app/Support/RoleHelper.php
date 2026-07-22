<?php

namespace App\Support;

class RoleHelper
{
    const GLOBAL_ROLES = ['ADMIN', 'KASIR_PUSAT', 'DIREKTUR'];

    const STAFF_AREA_ROLES = ['MAKER', 'AREA_MANAGER'];

    const PROJECT_SCOPED_ROLES = [
        'FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN',
        'KOORDINATOR_KEUANGAN', 'KOORDINATOR_PK', 'KOORDINATOR_TC',
        'KOORDINATOR_DIKLAT', 'KOORDINATOR_KLINIK', 'KOORDINATOR_BATRA',
        'KOORDINATOR_BIDANG',
    ];

    const PROJECT_FINANCE_ROLES = ['FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN'];

    const COORDINATOR_ROLES = [
        'KOORDINATOR_KEUANGAN', 'KOORDINATOR_PK', 'KOORDINATOR_TC',
        'KOORDINATOR_DIKLAT', 'KOORDINATOR_KLINIK', 'KOORDINATOR_BATRA',
        'KOORDINATOR_BIDANG',
    ];

    public static function isGlobal(?string $role): bool
    {
        return in_array($role, self::GLOBAL_ROLES, true);
    }

    public static function isStaffArea(?string $role): bool
    {
        return in_array($role, self::STAFF_AREA_ROLES, true);
    }

    public static function isProjectScoped(?string $role): bool
    {
        return in_array($role, self::PROJECT_SCOPED_ROLES, true);
    }

    public static function isProjectFinance(?string $role): bool
    {
        return in_array($role, self::PROJECT_FINANCE_ROLES, true);
    }

    public static function canAccessSpp(?string $role, ?string $userArea, ?string $userProject, object $surat): bool
    {
        if (self::isGlobal($role)) {
            return true;
        }

        if (self::isStaffArea($role) && $userArea === $surat->kode_area) {
            return true;
        }

        if ($role === 'MANAGER_KEUANGAN') {
            return true;
        }

        if (self::isProjectFinance($role) && $userProject === $surat->kode_project) {
            return true;
        }

        return false;
    }
}
