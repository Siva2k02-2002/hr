<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\TenantPermissions;

/**
 * Seeds the foundation permission catalog and the four system roles into a
 * tenant database. Runs bound to whatever connection it was constructed
 * with (see TenantProvisioningService) — never the default group.
 *
 * Idempotent: safe to run again against a database that was partially
 * provisioned by a previous failed attempt, or against an already-live
 * tenant to pick up newly added permissions (see "safe retry" requirement).
 */
class TenantRbacSeeder extends Seeder
{
    /** Phase 2 used singular slugs; Phase 3's spec uses plural. Renamed in place so existing grants survive. */
    private const RENAMES = [
        'user.view' => 'users.view', 'user.create' => 'users.create', 'user.edit' => 'users.edit',
        'role.view' => 'roles.view', 'role.create' => 'roles.create', 'role.edit' => 'roles.edit', 'role.delete' => 'roles.delete',
    ];

    /** @var array<string, array<string>|null> role slug => permission slugs (null = every permission) */
    private const ROLE_PERMISSIONS = [
        'company-admin' => null,
        'hr-manager'    => [
            'dashboard.view',
            'users.view', 'users.create', 'users.edit',
            'branches.view', 'departments.view', 'designations.view',
            'employee.view', 'employee.create', 'employee.edit', 'employee.export', 'employee.import',
            'employee.documents', 'employee.bank.view', 'employee.bank.edit', 'employee.status.change', 'employee.profile.view',
            'attendance.view', 'attendance.view.own', 'attendance.create', 'attendance.edit', 'attendance.approve',
            'attendance.export', 'attendance.import', 'attendance.shift.manage', 'attendance.location.manage',
            'attendance.device.manage', 'attendance.regularization.approve',
            'leave.view', 'leave.view.own', 'leave.create', 'leave.edit', 'leave.cancel', 'leave.cancel.any', 'leave.approve', 'leave.reject',
            'leave.balance.view', 'leave.balance.view.own', 'leave.balance.adjust', 'leave.policy.manage', 'leave.type.manage',
            'leave.settings.manage', 'leave.report.export',
            'reports.view', 'reports.export',
            'payroll.view', 'payroll.generate', 'payroll.edit', 'payroll.approve', 'payroll.export',
            'salarystructure.manage', 'salarycomponent.manage', 'loan.manage', 'advance.manage',
            'bonus.manage', 'reimbursement.manage', 'payslip.download', 'payslip.download.all',
        ],
        'manager' => [
            'dashboard.view',
            'employee.view', 'employee.profile.view',
            'attendance.view', 'attendance.view.own', 'attendance.approve', 'attendance.regularization.approve',
            'leave.view', 'leave.view.own', 'leave.create', 'leave.cancel', 'leave.cancel.any', 'leave.approve', 'leave.reject',
            'leave.balance.view', 'leave.balance.view.own',
            'reports.view',
        ],
        'employee' => [
            'dashboard.view',
            // .own slugs are the self-service variants — see the comments in
            // TenantPermissions.php. Employee must never hold the bare
            // attendance.view/leave.view/leave.balance.view (company-wide) slugs.
            'attendance.view.own',
            'leave.view.own', 'leave.create', 'leave.cancel', 'leave.balance.view.own',
            'employee.directory.search',
            // payslip.download (not payroll.view, and not payslip.download.all) is what gates
            // the My Payroll self-service screens — payroll.view is the admin/HR "see every
            // employee's pay" permission and payslip.download.all is the admin/HR "download
            // any employee's payslip" permission; both are deliberately never granted here.
            'payslip.download',
        ],
    ];

    private const ROLE_NAMES = [
        'company-admin' => 'Company Admin',
        'hr-manager'    => 'HR Manager',
        'manager'       => 'Manager',
        'employee'      => 'Employee',
    ];

    public function run()
    {
        $now = date('Y-m-d H:i:s');

        foreach (self::RENAMES as $old => $new) {
            $existingOld = $this->db->table('permissions')->where('slug', $old)->get()->getRowArray();
            $existingNew = $this->db->table('permissions')->where('slug', $new)->get()->getRowArray();
            if ($existingOld && ! $existingNew) {
                $this->db->table('permissions')->where('slug', $old)->update(['slug' => $new, 'updated_at' => $now]);
            } elseif ($existingOld && $existingNew) {
                // Both exist (e.g. re-run after a partial rename) — repoint any grants on the old
                // row to the new one, then drop the now-duplicate old permission row.
                $this->db->table('role_permissions')
                    ->where('permission_id', $existingOld['id'])
                    ->update(['permission_id' => $existingNew['id']]);
                $this->db->table('permissions')->where('id', $existingOld['id'])->delete();
            }
        }

        // --- Permissions, synced from the single source of truth ---------------
        $existingSlugs = array_column(
            $this->db->table('permissions')->select('slug')->get()->getResultArray(),
            'slug'
        );

        foreach (TenantPermissions::catalog() as $module => $actions) {
            foreach ($actions as $action => $description) {
                $slug = "{$module}.{$action}";
                if (in_array($slug, $existingSlugs, true)) {
                    continue;
                }
                $this->db->table('permissions')->insert([
                    'slug'        => $slug,
                    'module'      => $module,
                    'description' => $description,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        $permissionIdBySlug = array_column(
            $this->db->table('permissions')->select('id, slug')->get()->getResultArray(),
            'id',
            'slug'
        );

        // --- System roles ---------------------------------------------------------
        foreach (self::ROLE_PERMISSIONS as $slug => $grantedSlugs) {
            $role = $this->db->table('roles')->where('slug', $slug)->get()->getRowArray();

            if (! $role) {
                $this->db->table('roles')->insert([
                    'name'       => self::ROLE_NAMES[$slug],
                    'slug'       => $slug,
                    'is_system'  => 1,
                    'status'     => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $roleId = $this->db->insertID();
            } else {
                $roleId = $role['id'];
            }

            $grantIds = $grantedSlugs === null
                ? array_values($permissionIdBySlug)
                : array_values(array_intersect_key($permissionIdBySlug, array_flip($grantedSlugs)));

            $this->db->table('role_permissions')->where('role_id', $roleId)->delete();
            $rows = array_map(static fn ($id) => [
                'role_id'       => $roleId,
                'permission_id' => $id,
                'created_at'    => $now,
            ], $grantIds);
            if ($rows !== []) {
                $this->db->table('role_permissions')->insertBatch($rows);
            }
        }
    }
}
