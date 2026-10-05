<?php

namespace Config;

/**
 * Single source of truth for the tenant sidebar. The sidebar view never
 * hardcodes menu items or `can()` checks per-item — it just renders
 * whatever App\Services\MenuBuilder returns after filtering this tree
 * against the current user's permissions. Adding/removing a menu item
 * only ever means editing this file.
 *
 * Icon values are Lucide icon names (see icon() in app/Helpers/icon_helper.php).
 */
class TenantMenu
{
    public static function tree(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'icon'  => 'layout-dashboard',
                'route' => 'dashboard',
                'permission' => 'dashboard.view',
            ],
            [
                'label' => 'Employees',
                'icon'  => 'contact',
                'route' => 'employees',
                'permission' => 'employee.view',
            ],
            [
                'label' => 'Attendance',
                'children' => [
                    ['label' => 'Dashboard', 'icon' => 'gauge', 'route' => 'attendance/dashboard', 'permission' => 'attendance.view'],
                    ['label' => 'My Attendance', 'icon' => 'user-check', 'route' => 'my-attendance', 'permission' => 'attendance.view.own'],
                    ['label' => 'Attendance', 'icon' => 'calendar-check', 'route' => 'attendance', 'permission' => 'attendance.view'],
                    ['label' => 'Shifts', 'icon' => 'history', 'route' => 'attendance/shifts', 'permission' => 'attendance.shift.manage'],
                    ['label' => 'Shift Assignments', 'icon' => 'shuffle', 'route' => 'attendance/shift-assignments', 'permission' => 'attendance.shift.manage'],
                    ['label' => 'Weekly Off', 'icon' => 'calendar-x', 'route' => 'attendance/weekly-offs', 'permission' => 'attendance.view.own'],
                    ['label' => 'Holidays', 'icon' => 'calendar-clock', 'route' => 'attendance/holidays', 'permission' => 'attendance.view.own'],
                    ['label' => 'Locations', 'icon' => 'map-pin', 'route' => 'attendance/locations', 'permission' => 'attendance.view'],
                    ['label' => 'Devices', 'icon' => 'phone', 'route' => 'attendance/devices', 'permission' => 'attendance.device.manage'],
                    ['label' => 'Biometric Import', 'icon' => 'fingerprint', 'route' => 'attendance/biometric', 'permission' => 'attendance.import'],
                    ['label' => 'Excel Import', 'icon' => 'file-spreadsheet', 'route' => 'attendance/import', 'permission' => 'attendance.import'],
                    ['label' => 'Regularizations', 'icon' => 'square-pen', 'route' => 'attendance/regularizations', 'permission' => 'attendance.regularization.approve'],
                    ['label' => 'Request Regularization', 'icon' => 'square-pen', 'route' => 'attendance/regularizations/new', 'permission' => 'attendance.view.own'],
                    ['label' => 'Reports', 'icon' => 'bar-chart-3', 'route' => 'attendance/reports', 'permission' => 'attendance.view'],
                    ['label' => 'Settings', 'icon' => 'settings', 'route' => 'attendance/settings', 'permission' => 'attendance.settings.manage'],
                ],
            ],
            [
                'label' => 'Leave',
                'children' => [
                    ['label' => 'My Leave', 'icon' => 'user-check', 'route' => 'my-leave', 'permission' => 'leave.view.own'],
                    ['label' => 'Leave Applications', 'icon' => 'calendar-days', 'route' => 'leave', 'permission' => 'leave.view'],
                    ['label' => 'Leave Calendar', 'icon' => 'calendar', 'route' => 'leave/calendar', 'permission' => 'leave.view'],
                    ['label' => 'Balances', 'icon' => 'wallet', 'route' => 'leave/balances', 'permission' => 'leave.balance.view'],
                    ['label' => 'Carry Forward', 'icon' => 'repeat', 'route' => 'leave/carry-forward', 'permission' => 'leave.balance.adjust'],
                    ['label' => 'Encashment', 'icon' => 'coins', 'route' => 'leave/encashments', 'permission' => 'leave.view'],
                    ['label' => 'Leave Types', 'icon' => 'tags', 'route' => 'leave/types', 'permission' => 'leave.type.manage'],
                    ['label' => 'Leave Policies', 'icon' => 'notebook-text', 'route' => 'leave/policies', 'permission' => 'leave.policy.manage'],
                    ['label' => 'Reports', 'icon' => 'bar-chart-3', 'route' => 'leave/reports', 'permission' => 'leave.view'],
                    ['label' => 'Settings', 'icon' => 'settings', 'route' => 'leave/settings', 'permission' => 'leave.settings.manage'],
                ],
            ],
            [
                'label' => 'Payroll',
                'children' => [
                    ['label' => 'My Payroll', 'icon' => 'user-check', 'route' => 'my-payroll', 'permission' => 'payslip.download'],
                    ['label' => 'Dashboard', 'icon' => 'gauge', 'route' => 'payroll/dashboard', 'permission' => 'payroll.view'],
                    ['label' => 'Payroll Runs', 'icon' => 'circle-play', 'route' => 'payroll/runs', 'permission' => 'payroll.view'],
                    ['label' => 'Salary Components', 'icon' => 'wallet', 'route' => 'payroll/components', 'permission' => 'salarycomponent.manage'],
                    ['label' => 'Salary Structures', 'icon' => 'network', 'route' => 'payroll/structures', 'permission' => 'salarystructure.manage'],
                    ['label' => 'Employee Salary', 'icon' => 'wallet', 'route' => 'payroll/employee-salary', 'permission' => 'salarystructure.manage'],
                    ['label' => 'Loans', 'icon' => 'coins', 'route' => 'payroll/loans', 'permission' => 'loan.manage'],
                    ['label' => 'Advances', 'icon' => 'banknote', 'route' => 'payroll/advances', 'permission' => 'advance.manage'],
                    ['label' => 'Bonus', 'icon' => 'gift', 'route' => 'payroll/bonus', 'permission' => 'bonus.manage'],
                    ['label' => 'Incentives', 'icon' => 'star', 'route' => 'payroll/incentives', 'permission' => 'bonus.manage'],
                    ['label' => 'Reimbursements', 'icon' => 'receipt', 'route' => 'payroll/reimbursements', 'permission' => 'reimbursement.manage'],
                    ['label' => 'Arrears', 'icon' => 'repeat', 'route' => 'payroll/arrears', 'permission' => 'payroll.edit'],
                    ['label' => 'Payslips', 'icon' => 'file-text', 'route' => 'payroll/payslips', 'permission' => 'payroll.view'],
                    ['label' => 'Reports', 'icon' => 'bar-chart-3', 'route' => 'payroll/reports', 'permission' => 'payroll.view'],
                    ['label' => 'Settings', 'icon' => 'settings', 'route' => 'payroll/settings', 'permission' => 'payroll.settings.manage'],
                ],
            ],
            [
                'label' => 'Organization',
                'children' => [
                    ['label' => 'Branches', 'icon' => 'network', 'route' => 'branches', 'permission' => 'branches.view'],
                    ['label' => 'Departments', 'icon' => 'building-2', 'route' => 'departments', 'permission' => 'departments.view'],
                    ['label' => 'Designations', 'icon' => 'id-card', 'route' => 'designations', 'permission' => 'designations.view'],
                ],
            ],
            [
                'label' => 'Administration',
                'children' => [
                    ['label' => 'Users', 'icon' => 'users', 'route' => 'users', 'permission' => 'users.view'],
                    ['label' => 'Roles', 'icon' => 'shield-check', 'route' => 'roles', 'permission' => 'roles.view'],
                    ['label' => 'Permissions', 'icon' => 'key-round', 'route' => 'permissions', 'permission' => 'roles.view'],
                ],
            ],
            [
                'label' => 'Settings',
                'children' => [
                    ['label' => 'Company Settings', 'icon' => 'settings', 'route' => 'settings', 'permission' => 'settings.view'],
                    ['label' => 'Audit Logs', 'icon' => 'history', 'route' => 'audit-logs', 'permission' => 'audit.view'],
                ],
            ],
        ];
    }
}
