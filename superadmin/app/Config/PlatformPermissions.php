<?php

namespace Config;

/**
 * Single source of truth for platform permission slugs.
 *
 * The `platform_permissions` table is synced to this array by
 * App\Database\Seeds\PlatformRbacSeeder — the table is never hand-edited,
 * so code and database can never drift apart.
 */
class PlatformPermissions
{
    public static function catalog(): array
    {
        return [
            'company' => [
                'view'     => 'View companies',
                'create'   => 'Create companies',
                'edit'     => 'Edit company details',
                'delete'   => 'Archive companies',
                'activate' => 'Activate companies',
                'suspend'  => 'Suspend companies',
                'settings' => "Manage a company's tenant settings (branding, localization, theme colors)",
            ],
            'plan' => [
                'view'   => 'View plans',
                'create' => 'Create plans',
                'edit'   => 'Edit plans',
                'delete' => 'Deactivate plans',
            ],
            'module' => [
                'view'   => 'View modules',
                'manage' => 'Manage module catalog and plan/company assignment',
            ],
            'subscription' => [
                'view'    => 'View subscriptions',
                'create'  => 'Create subscriptions',
                'edit'    => 'Edit subscriptions',
                'extend'  => 'Extend or renew subscriptions',
                'suspend' => 'Suspend or cancel subscriptions',
            ],
            'user' => [
                'view'   => 'View platform users',
                'manage' => 'Create, edit, and deactivate platform users',
            ],
            'role' => [
                'view'   => 'View roles and permissions',
                'manage' => 'Create and edit roles, assign permissions',
            ],
            'audit' => [
                'view' => 'View audit and login logs',
            ],
            'settings' => [
                'manage' => 'Manage platform settings',
            ],
        ];
    }

    /** Flat list of "module.action" slugs. */
    public static function slugs(): array
    {
        $slugs = [];
        foreach (self::catalog() as $module => $actions) {
            foreach (array_keys($actions) as $action) {
                $slugs[] = "{$module}.{$action}";
            }
        }

        return $slugs;
    }
}
