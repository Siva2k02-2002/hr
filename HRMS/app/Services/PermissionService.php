<?php

namespace App\Services;

use App\Models\CompanySettingModel;

/**
 * Same single-code-path pattern as superadmin: route filter and view
 * helper both call can(), reading the session-cached permission list.
 *
 * The list is computed once at login and cached in session for the
 * lifetime of that session — refreshIfStale() (called from
 * TenantAuthFilter on every authenticated request) is what keeps it
 * correct without re-querying role/permission joins every request: it
 * compares one indexed company_settings.permissions_version lookup
 * against the version the cache was built with, and only recomputes on
 * mismatch.
 */
class PermissionService
{
    public function slugsForUser(int $userId): array
    {
        $roleIds = array_column(
            service('tenantContext')->db()->table('user_roles')->select('role_id')->where('user_id', $userId)->get()->getResultArray(),
            'role_id'
        );

        if ($roleIds === []) {
            return [];
        }

        $rows = service('tenantContext')->db()->table('role_permissions rp')
            ->select('p.slug')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->whereIn('rp.role_id', $roleIds)
            ->get()
            ->getResultArray();

        return array_values(array_unique(array_column($rows, 'slug')));
    }

    public function can(string $slug): bool
    {
        $granted = session('tenant_permissions') ?? [];

        return in_array($slug, $granted, true);
    }

    public function refreshIfStale(int $userId): void
    {
        $current = (int) (new CompanySettingModel(service('tenantContext')->db()))->current()['permissions_version'] ?? 1;
        $cached  = (int) session('tenant_permissions_version');

        if ($cached === $current) {
            return;
        }

        session()->set([
            'tenant_permissions'         => $this->slugsForUser($userId),
            'tenant_permissions_version' => $current,
        ]);
    }
}
