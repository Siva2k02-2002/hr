<?php

namespace Config;

/**
 * Single source of truth for tenant-side permission slugs. Seeded into
 * every tenant database by TenantRbacSeeder — never hand-edited in the
 * DB, to prevent code/DB drift.
 *
 * Business-module permissions (employee/attendance/leave/payroll/assets)
 * were declared here ahead of their modules existing, per the Phase 3
 * spec's explicit instruction to pre-declare the full permission surface
 * so later phases could consume it without touching this file again.
 * employee/attendance/leave/payroll are now live (Phases 4-7); assets
 * remains an inert placeholder until its phase builds the controllers/
 * routes that check it. The Phase 3 placeholder `payroll.view/process/
 * approve` block has been replaced by the full Phase 7 slug set below —
 * `payroll.process` is superseded by `payroll.generate`; TenantRbacSeeder
 * never deletes a slug, so the old orphan row harmlessly persists,
 * unreferenced and ungranted, in any tenant provisioned before Phase 7.
 */
class TenantPermissions
{
    public static function catalog(): array
    {
        return [
            'dashboard' => [
                'view' => 'View dashboard',
            ],
            'users' => [
                'view'   => 'View users',
                'create' => 'Create users',
                'edit'   => 'Edit users',
                'delete' => 'Delete users',
            ],
            'roles' => [
                'view'   => 'View roles',
                'create' => 'Create roles',
                'edit'   => 'Edit roles',
                'delete' => 'Delete roles',
            ],
            'branches' => [
                'view'   => 'View branches',
                'create' => 'Create branches',
                'edit'   => 'Edit branches',
                'delete' => 'Delete branches',
            ],
            'departments' => [
                'view'   => 'View departments',
                'create' => 'Create departments',
                'edit'   => 'Edit departments',
                'delete' => 'Delete departments',
            ],
            'designations' => [
                'view'   => 'View designations',
                'create' => 'Create designations',
                'edit'   => 'Edit designations',
                'delete' => 'Delete designations',
            ],
            'settings' => [
                'view'   => 'View company settings',
                'manage' => 'Manage company settings',
            ],
            'audit' => [
                'view' => 'View audit logs',
            ],
            'employee' => [
                'view' => 'View employees', 'create' => 'Create employees', 'edit' => 'Edit employees',
                'delete' => 'Delete employees', 'export' => 'Export employees', 'import' => 'Import employees',
                'documents' => 'Manage employee documents',
                'bank.view' => 'View employee bank details', 'bank.edit' => 'Edit employee bank details',
                'status.change' => 'Change employee status', 'profile.view' => 'View employee 360 profile',
                // Lets any authenticated user use the global "search employees" command
                // palette (name/code lookup only). Deliberately separate from employee.view
                // (the full company directory/admin screen) so granting it to Employee can
                // never unlock EmployeesController's admin listing.
                'directory.search' => 'Search the employee directory (name lookup only)',
            ],
            'attendance' => [
                // 'view' is the admin/HR-wide "see every employee's attendance" permission
                // (company-wide list, dashboard, overtime, reports). 'view.own' is the
                // self-service "see my own attendance" permission used by My Attendance and
                // the shared employee-facing reference screens (Weekly Off, Holidays) —
                // deliberately a separate slug so granting self-service view to the plain
                // employee role can never unlock the company-wide screens.
                'view' => 'View attendance', 'view.own' => 'View own attendance (self-service)',
                'create' => 'Record attendance', 'edit' => 'Edit attendance',
                'delete' => 'Delete attendance', 'approve' => 'Approve attendance',
                'export' => 'Export attendance', 'import' => 'Import attendance',
                'settings.manage' => 'Manage attendance settings', 'shift.manage' => 'Manage shifts',
                'location.manage' => 'Manage office locations', 'device.manage' => 'Manage attendance devices',
                'regularization.approve' => 'Approve attendance regularizations',
            ],
            'leave' => [
                // 'view' is the admin/HR-wide "see every employee's leave" permission (company-
                // wide applications list, calendar, encashments list, reports). 'view.own' is
                // the self-service "see my own leave" permission used by My Leave — deliberately
                // a separate slug, same reasoning as attendance.view/attendance.view.own below
                // and leave.cancel/leave.cancel.any above.
                'view' => 'View leave applications', 'view.own' => 'View own leave applications (self-service)',
                'create' => 'Apply for leave', 'edit' => 'Edit leave applications',
                // 'cancel' is the self-service "cancel my own application" permission (My Leave).
                // 'cancel.any' is the HR/manager-side "cancel any employee's application" permission
                // used by the admin Leave Applications list — deliberately a separate slug so granting
                // self-service cancel to the plain employee role can never unlock cancelling others'.
                'cancel' => 'Cancel own leave applications', 'cancel.any' => "Cancel any employee's leave applications",
                'approve' => 'Approve leave applications', 'reject' => 'Reject leave applications',
                // 'balance.view' is the admin/HR-wide "see every employee's leave balance"
                // permission (Balances screen). 'balance.view.own' is the self-service "see my
                // own balance" permission used by My Leave — same own/all split as above.
                'balance.view' => 'View leave balances', 'balance.view.own' => 'View own leave balance (self-service)',
                'balance.adjust' => 'Adjust leave balances',
                'policy.manage' => 'Manage leave policies', 'type.manage' => 'Manage leave types',
                'settings.manage' => 'Manage leave settings', 'report.export' => 'Export leave reports',
            ],
            'payroll' => [
                // 'view' is the admin/HR-side "see the company's payroll runs and every
                // employee's pay" permission — never grant it to the plain employee role.
                // Employee self-service (My Payroll) is gated by payslip.download instead.
                'view' => 'View payroll', 'generate' => 'Generate payroll', 'edit' => 'Edit payroll',
                'approve' => 'Approve payroll', 'lock' => 'Lock/unlock payroll', 'pay' => 'Mark payroll as paid',
                'export' => 'Export payroll reports', 'settings.manage' => 'Manage payroll settings',
            ],
            'salarystructure' => [
                'manage' => 'Manage salary structures',
            ],
            'salarycomponent' => [
                'manage' => 'Manage salary components',
            ],
            'loan' => [
                'manage' => 'Manage employee loans',
            ],
            'advance' => [
                'manage' => 'Manage salary advances',
            ],
            'bonus' => [
                'manage' => 'Manage bonus and incentives',
            ],
            'reimbursement' => [
                'manage' => 'Manage reimbursements',
            ],
            'payslip' => [
                // 'download' is the self-service "download my own payslip" permission used by
                // My Payroll. 'download.all' is the HR/admin-side permission that gates
                // downloading (or bulk-zipping) any employee's payslip from the admin Payslips
                // screen — deliberately a separate slug so granting self-service download to
                // the plain employee role can never unlock other employees' payslips.
                'download' => 'Download own payslip', 'download.all' => "Download any employee's payslip (administrative)",
            ],
            // --- Not yet implemented — declared for Phase 8+ ---
            'assets' => [
                'view' => 'View assets', 'create' => 'Create assets',
            ],
            'reports' => [
                'view' => 'View reports', 'export' => 'Export reports',
            ],
        ];
    }

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
