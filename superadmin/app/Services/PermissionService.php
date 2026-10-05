<?php

namespace App\Services;

use App\Models\PlatformUserModel;

/**
 * One code path for every permission check — route filters and view-level
 * button gating both call can(), so there is never a second implementation
 * of "does this user have this permission" to drift out of sync.
 */
class PermissionService
{
    /**
     * Permission slugs for a user, deduplicated across every role they hold.
     */
    public function slugsForUser(int $userId): array
    {
        $roleIds = array_column(
            (new PlatformUserModel())->db->table('platform_user_roles')
                ->select('role_id')
                ->where('user_id', $userId)
                ->get()
                ->getResultArray(),
            'role_id'
        );

        if ($roleIds === []) {
            return [];
        }

        $rows = (new PlatformUserModel())->db->table('platform_role_permissions rp')
            ->select('p.slug')
            ->join('platform_permissions p', 'p.id = rp.permission_id')
            ->whereIn('rp.role_id', $roleIds)
            ->get()
            ->getResultArray();

        return array_values(array_unique(array_column($rows, 'slug')));
    }

    /** Checks the currently logged-in user's session-cached permission list. */
    public function can(string $slug): bool
    {
        $granted = session('platform_permissions') ?? [];

        return in_array($slug, $granted, true);
    }
}
