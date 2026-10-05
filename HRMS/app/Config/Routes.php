<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Every other route is tenant-resolved from the request hostname first.
$routes->group('', ['filter' => 'tenant'], static function (RouteCollection $routes) {
    $routes->get('/', 'AuthController::showLogin');
    $routes->get('login', 'AuthController::showLogin');
    $routes->post('login', 'AuthController::login');
    $routes->post('logout', 'AuthController::logout');
    $routes->get('forgot-password', 'AuthController::showForgotPassword');
    $routes->post('forgot-password', 'AuthController::forgotPassword');
    $routes->get('reset-password/(:segment)', 'AuthController::showResetPassword/$1');
    $routes->post('reset-password/(:segment)', 'AuthController::resetPassword/$1');
    $routes->get('verify-email/(:segment)', 'EmailVerificationController::verify/$1');
    $routes->get('branding/(:segment)', 'CompanyBrandingController::show/$1');

    $routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
        $routes->get('dashboard', 'DashboardController::index', ['filter' => 'permission:dashboard.view']);

        $routes->get('change-password', 'AuthController::showChangePassword');
        $routes->post('change-password', 'AuthController::changePassword');
        $routes->post('logout-other-devices', 'AuthController::logoutOtherDevices');

        $routes->get('profile', 'ProfileController::index');
        $routes->post('profile', 'ProfileController::update');

        $routes->get('notifications', 'NotificationsController::index');
        $routes->get('notifications/(:num)/open', 'NotificationsController::open/$1');
        $routes->post('notifications/mark-all-read', 'NotificationsController::markAllRead');

        // Organization
        $routes->get('branches', 'BranchesController::index', ['filter' => 'permission:branches.view']);
        $routes->get('branches/archived', 'BranchesController::archived', ['filter' => 'permission:branches.delete']);
        $routes->post('branches/(:num)/restore', 'BranchesController::restore/$1', ['filter' => 'permission:branches.delete']);
        $routes->get('branches/create', 'BranchesController::create', ['filter' => 'permission:branches.create']);
        $routes->post('branches', 'BranchesController::store', ['filter' => 'permission:branches.create']);
        $routes->get('branches/(:num)/edit', 'BranchesController::edit/$1', ['filter' => 'permission:branches.edit']);
        $routes->post('branches/(:num)', 'BranchesController::update/$1', ['filter' => 'permission:branches.edit']);
        $routes->post('branches/(:num)/delete', 'BranchesController::delete/$1', ['filter' => 'permission:branches.delete']);

        $routes->get('departments', 'DepartmentsController::index', ['filter' => 'permission:departments.view']);
        $routes->get('departments/archived', 'DepartmentsController::archived', ['filter' => 'permission:departments.delete']);
        $routes->post('departments/(:num)/restore', 'DepartmentsController::restore/$1', ['filter' => 'permission:departments.delete']);
        $routes->get('departments/create', 'DepartmentsController::create', ['filter' => 'permission:departments.create']);
        $routes->post('departments', 'DepartmentsController::store', ['filter' => 'permission:departments.create']);
        $routes->get('departments/(:num)/edit', 'DepartmentsController::edit/$1', ['filter' => 'permission:departments.edit']);
        $routes->post('departments/(:num)', 'DepartmentsController::update/$1', ['filter' => 'permission:departments.edit']);
        $routes->post('departments/(:num)/delete', 'DepartmentsController::delete/$1', ['filter' => 'permission:departments.delete']);

        $routes->get('designations', 'DesignationsController::index', ['filter' => 'permission:designations.view']);
        $routes->get('designations/archived', 'DesignationsController::archived', ['filter' => 'permission:designations.delete']);
        $routes->post('designations/(:num)/restore', 'DesignationsController::restore/$1', ['filter' => 'permission:designations.delete']);
        $routes->get('designations/create', 'DesignationsController::create', ['filter' => 'permission:designations.create']);
        $routes->post('designations', 'DesignationsController::store', ['filter' => 'permission:designations.create']);
        $routes->get('designations/(:num)/edit', 'DesignationsController::edit/$1', ['filter' => 'permission:designations.edit']);
        $routes->post('designations/(:num)', 'DesignationsController::update/$1', ['filter' => 'permission:designations.edit']);
        $routes->post('designations/(:num)/delete', 'DesignationsController::delete/$1', ['filter' => 'permission:designations.delete']);

        // Employee Master
        $routes->get('employees', 'EmployeesController::index', ['filter' => 'permission:employee.view']);
        $routes->get('employees/archived', 'EmployeesController::archived', ['filter' => 'permission:employee.delete']);
        $routes->post('employees/(:num)/restore', 'EmployeesController::restore/$1', ['filter' => 'permission:employee.delete']);
        $routes->get('employees/create', 'EmployeesController::create', ['filter' => 'permission:employee.create']);
        $routes->post('employees', 'EmployeesController::store', ['filter' => 'permission:employee.create']);
        $routes->get('employees/import', 'EmployeeImportController::form', ['filter' => 'permission:employee.import']);
        $routes->get('employees/import/template', 'EmployeeImportController::template', ['filter' => 'permission:employee.import']);
        $routes->post('employees/import/preview', 'EmployeeImportController::preview', ['filter' => 'permission:employee.import']);
        $routes->post('employees/import/commit', 'EmployeeImportController::commit', ['filter' => 'permission:employee.import']);
        $routes->get('employees/export/excel', 'EmployeeExportController::excel', ['filter' => 'permission:employee.export']);
        $routes->get('employees/export/csv', 'EmployeeExportController::csv', ['filter' => 'permission:employee.export']);
        $routes->get('employees/export/pdf', 'EmployeeExportController::pdf', ['filter' => 'permission:employee.export']);

        $routes->get('employees/(:num)', 'EmployeesController::view/$1', ['filter' => 'permission:employee.profile.view']);
        $routes->get('employees/(:num)/pdf', 'EmployeesController::pdf/$1', ['filter' => 'permission:employee.profile.view']);
        $routes->get('employees/(:num)/tab/(:segment)', 'EmployeesController::tab/$1/$2', ['filter' => 'permission:employee.profile.view']);
        $routes->post('employees/(:num)/create-login', 'EmployeesController::createLogin/$1', ['filter' => 'permission:users.create']);
        $routes->get('employees/(:num)/edit', 'EmployeesController::edit/$1', ['filter' => 'permission:employee.edit']);
        $routes->post('employees/(:num)', 'EmployeesController::update/$1', ['filter' => 'permission:employee.edit']);
        $routes->post('employees/(:num)/delete', 'EmployeesController::delete/$1', ['filter' => 'permission:employee.delete']);

        $routes->post('employees/(:num)/activate', 'EmployeeStatusController::activate/$1', ['filter' => 'permission:employee.status.change']);
        $routes->post('employees/(:num)/suspend', 'EmployeeStatusController::suspend/$1', ['filter' => 'permission:employee.status.change']);
        $routes->post('employees/(:num)/relieve', 'EmployeeStatusController::relieve/$1', ['filter' => 'permission:employee.status.change']);
        $routes->post('employees/(:num)/rejoin', 'EmployeeStatusController::rejoin/$1', ['filter' => 'permission:employee.status.change']);
        $routes->post('employees/(:num)/status', 'EmployeeStatusController::change/$1', ['filter' => 'permission:employee.status.change']);

        $routes->post('employees/(:num)/photo', 'EmployeePhotoController::upload/$1', ['filter' => 'permission:employee.edit']);
        $routes->get('employees/(:num)/photo', 'EmployeePhotoController::show/$1', ['filter' => 'permission:employee.profile.view']);

        $routes->post('employees/(:num)/address', 'EmployeeAddressController::save/$1', ['filter' => 'permission:employee.edit']);

        $routes->post('employees/(:num)/emergency-contacts', 'EmployeeEmergencyContactsController::store/$1', ['filter' => 'permission:employee.edit']);
        $routes->post('employees/(:num)/emergency-contacts/(:num)', 'EmployeeEmergencyContactsController::update/$1/$2', ['filter' => 'permission:employee.edit']);
        $routes->post('employees/(:num)/emergency-contacts/(:num)/delete', 'EmployeeEmergencyContactsController::delete/$1/$2', ['filter' => 'permission:employee.edit']);

        $routes->post('employees/(:num)/family', 'EmployeeFamilyController::store/$1', ['filter' => 'permission:employee.edit']);
        $routes->post('employees/(:num)/family/(:num)', 'EmployeeFamilyController::update/$1/$2', ['filter' => 'permission:employee.edit']);
        $routes->post('employees/(:num)/family/(:num)/delete', 'EmployeeFamilyController::delete/$1/$2', ['filter' => 'permission:employee.edit']);

        $routes->post('employees/(:num)/education', 'EmployeeEducationController::store/$1', ['filter' => 'permission:employee.edit']);
        $routes->post('employees/(:num)/education/(:num)', 'EmployeeEducationController::update/$1/$2', ['filter' => 'permission:employee.edit']);
        $routes->post('employees/(:num)/education/(:num)/delete', 'EmployeeEducationController::delete/$1/$2', ['filter' => 'permission:employee.edit']);

        $routes->post('employees/(:num)/experience', 'EmployeeExperienceController::store/$1', ['filter' => 'permission:employee.edit']);
        $routes->post('employees/(:num)/experience/(:num)', 'EmployeeExperienceController::update/$1/$2', ['filter' => 'permission:employee.edit']);
        $routes->post('employees/(:num)/experience/(:num)/delete', 'EmployeeExperienceController::delete/$1/$2', ['filter' => 'permission:employee.edit']);

        $routes->post('employees/(:num)/bank', 'EmployeeBankController::store/$1', ['filter' => 'permission:employee.bank.edit']);
        $routes->post('employees/(:num)/bank/(:num)', 'EmployeeBankController::update/$1/$2', ['filter' => 'permission:employee.bank.edit']);
        $routes->post('employees/(:num)/bank/(:num)/delete', 'EmployeeBankController::delete/$1/$2', ['filter' => 'permission:employee.bank.edit']);

        $routes->post('employees/(:num)/documents', 'EmployeeDocumentsController::store/$1', ['filter' => 'permission:employee.documents']);
        $routes->post('employees/(:num)/documents/(:num)/delete', 'EmployeeDocumentsController::delete/$1/$2', ['filter' => 'permission:employee.documents']);
        $routes->get('employees/(:num)/documents/(:num)/download', 'EmployeeDocumentsController::download/$1/$2', ['filter' => 'permission:employee.documents']);

        // Attendance
        $routes->get('attendance/dashboard', 'AttendanceDashboardController::index', ['filter' => 'permission:attendance.view']);

        $routes->get('my-attendance', 'MyAttendanceController::index', ['filter' => 'permission:attendance.view.own']);
        $routes->post('my-attendance/punch', 'MyAttendanceController::punch', ['filter' => 'permission:attendance.view.own']);

        $routes->get('attendance', 'AttendanceController::index', ['filter' => 'permission:attendance.view']);
        $routes->get('attendance/bulk', 'AttendanceController::bulkForm', ['filter' => 'permission:attendance.create']);
        $routes->post('attendance/bulk', 'AttendanceController::bulkMark', ['filter' => 'permission:attendance.create']);
        $routes->get('attendance/import', 'AttendanceImportController::form', ['filter' => 'permission:attendance.import']);
        $routes->get('attendance/import/template', 'AttendanceImportController::template', ['filter' => 'permission:attendance.import']);
        $routes->post('attendance/import/preview', 'AttendanceImportController::preview', ['filter' => 'permission:attendance.import']);
        $routes->post('attendance/import/commit', 'AttendanceImportController::commit', ['filter' => 'permission:attendance.import']);

        $routes->get('attendance/(:num)/edit', 'AttendanceController::edit/$1', ['filter' => 'permission:attendance.edit']);
        $routes->post('attendance/(:num)', 'AttendanceController::update/$1', ['filter' => 'permission:attendance.edit']);
        $routes->post('attendance/(:num)/delete', 'AttendanceController::delete/$1', ['filter' => 'permission:attendance.delete']);

        $routes->get('attendance/shifts', 'AttendanceShiftsController::index', ['filter' => 'permission:attendance.shift.manage']);
        $routes->get('attendance/shifts/create', 'AttendanceShiftsController::create', ['filter' => 'permission:attendance.shift.manage']);
        $routes->post('attendance/shifts', 'AttendanceShiftsController::store', ['filter' => 'permission:attendance.shift.manage']);
        $routes->get('attendance/shifts/(:num)/edit', 'AttendanceShiftsController::edit/$1', ['filter' => 'permission:attendance.shift.manage']);
        $routes->post('attendance/shifts/(:num)', 'AttendanceShiftsController::update/$1', ['filter' => 'permission:attendance.shift.manage']);
        $routes->post('attendance/shifts/(:num)/delete', 'AttendanceShiftsController::delete/$1', ['filter' => 'permission:attendance.shift.manage']);

        $routes->get('attendance/shift-assignments', 'AttendanceShiftAssignmentsController::form', ['filter' => 'permission:attendance.shift.manage']);
        $routes->post('attendance/shift-assignments', 'AttendanceShiftAssignmentsController::assign', ['filter' => 'permission:attendance.shift.manage']);
        $routes->get('attendance/shift-assignments/(:num)/history', 'AttendanceShiftAssignmentsController::history/$1', ['filter' => 'permission:attendance.shift.manage']);

        $routes->get('attendance/weekly-offs', 'AttendanceWeeklyOffsController::index', ['filter' => 'permission:attendance.view.own']);
        $routes->get('attendance/weekly-offs/create', 'AttendanceWeeklyOffsController::create', ['filter' => 'permission:attendance.edit']);
        $routes->post('attendance/weekly-offs', 'AttendanceWeeklyOffsController::store', ['filter' => 'permission:attendance.edit']);
        $routes->get('attendance/weekly-offs/(:num)/edit', 'AttendanceWeeklyOffsController::edit/$1', ['filter' => 'permission:attendance.edit']);
        $routes->post('attendance/weekly-offs/(:num)', 'AttendanceWeeklyOffsController::update/$1', ['filter' => 'permission:attendance.edit']);
        $routes->post('attendance/weekly-offs/(:num)/delete', 'AttendanceWeeklyOffsController::delete/$1', ['filter' => 'permission:attendance.edit']);

        $routes->get('attendance/holidays', 'AttendanceHolidaysController::index', ['filter' => 'permission:attendance.view.own']);
        $routes->get('attendance/holidays/calendar', 'AttendanceHolidaysController::calendar', ['filter' => 'permission:attendance.view.own']);
        $routes->get('attendance/holidays/create', 'AttendanceHolidaysController::create', ['filter' => 'permission:attendance.edit']);
        $routes->post('attendance/holidays', 'AttendanceHolidaysController::store', ['filter' => 'permission:attendance.edit']);
        $routes->get('attendance/holidays/import', 'AttendanceHolidaysController::importForm', ['filter' => 'permission:attendance.import']);
        $routes->get('attendance/holidays/import/template', 'AttendanceHolidaysController::importTemplate', ['filter' => 'permission:attendance.import']);
        $routes->post('attendance/holidays/import', 'AttendanceHolidaysController::importCommit', ['filter' => 'permission:attendance.import']);
        $routes->get('attendance/holidays/generate-next-year', 'AttendanceHolidaysController::generateNextYearPreview', ['filter' => 'permission:attendance.edit']);
        $routes->post('attendance/holidays/generate-next-year', 'AttendanceHolidaysController::generateNextYearCommit', ['filter' => 'permission:attendance.edit']);
        $routes->get('attendance/holidays/(:num)/edit', 'AttendanceHolidaysController::edit/$1', ['filter' => 'permission:attendance.edit']);
        $routes->post('attendance/holidays/(:num)', 'AttendanceHolidaysController::update/$1', ['filter' => 'permission:attendance.edit']);
        $routes->post('attendance/holidays/(:num)/delete', 'AttendanceHolidaysController::delete/$1', ['filter' => 'permission:attendance.edit']);

        $routes->get('attendance/locations', 'AttendanceLocationsController::index', ['filter' => 'permission:attendance.view']);
        $routes->get('attendance/locations/create', 'AttendanceLocationsController::create', ['filter' => 'permission:attendance.location.manage']);
        $routes->post('attendance/locations', 'AttendanceLocationsController::store', ['filter' => 'permission:attendance.location.manage']);
        $routes->get('attendance/locations/(:num)/edit', 'AttendanceLocationsController::edit/$1', ['filter' => 'permission:attendance.location.manage']);
        $routes->post('attendance/locations/(:num)', 'AttendanceLocationsController::update/$1', ['filter' => 'permission:attendance.location.manage']);
        $routes->post('attendance/locations/(:num)/delete', 'AttendanceLocationsController::delete/$1', ['filter' => 'permission:attendance.location.manage']);

        $routes->get('attendance/devices', 'AttendanceDevicesController::index', ['filter' => 'permission:attendance.device.manage']);
        $routes->post('attendance/devices/(:num)/approve', 'AttendanceDevicesController::approve/$1', ['filter' => 'permission:attendance.device.manage']);
        $routes->post('attendance/devices/(:num)/reject', 'AttendanceDevicesController::reject/$1', ['filter' => 'permission:attendance.device.manage']);
        $routes->post('attendance/devices/(:num)/block', 'AttendanceDevicesController::block/$1', ['filter' => 'permission:attendance.device.manage']);

        $routes->get('attendance/regularizations', 'AttendanceRegularizationsController::index', ['filter' => 'permission:attendance.regularization.approve']);
        $routes->get('attendance/regularizations/new', 'AttendanceRegularizationsController::form', ['filter' => 'permission:attendance.view.own']);
        $routes->post('attendance/regularizations', 'AttendanceRegularizationsController::store', ['filter' => 'permission:attendance.view.own']);
        $routes->post('attendance/regularizations/(:num)/approve', 'AttendanceRegularizationsController::approve/$1', ['filter' => 'permission:attendance.regularization.approve']);
        $routes->post('attendance/regularizations/(:num)/reject', 'AttendanceRegularizationsController::reject/$1', ['filter' => 'permission:attendance.regularization.approve']);

        $routes->get('attendance/overtime', 'AttendanceOvertimeController::index', ['filter' => 'permission:attendance.view']);
        $routes->post('attendance/overtime/(:num)/approve', 'AttendanceOvertimeController::approve/$1', ['filter' => 'permission:attendance.approve']);
        $routes->post('attendance/overtime/(:num)/reject', 'AttendanceOvertimeController::reject/$1', ['filter' => 'permission:attendance.approve']);

        $routes->get('attendance/reports', 'AttendanceReportsController::index', ['filter' => 'permission:attendance.view']);
        $routes->get('attendance/reports/(:segment)', 'AttendanceReportsController::view/$1', ['filter' => 'permission:attendance.view']);
        $routes->get('attendance/reports/(:segment)/export/(:segment)', 'AttendanceReportsController::export/$1/$2', ['filter' => 'permission:attendance.export']);

        $routes->get('attendance/biometric', 'AttendanceBiometricController::index', ['filter' => 'permission:attendance.import']);
        $routes->post('attendance/biometric/stage', 'AttendanceBiometricController::stage', ['filter' => 'permission:attendance.import']);
        $routes->post('attendance/biometric/sync', 'AttendanceBiometricController::sync', ['filter' => 'permission:attendance.import']);

        $routes->get('attendance/settings', 'AttendanceSettingsController::index', ['filter' => 'permission:attendance.settings.manage']);
        $routes->post('attendance/settings', 'AttendanceSettingsController::update', ['filter' => 'permission:attendance.settings.manage']);

        // Leave
        $routes->get('leave/settings', 'LeaveSettingsController::index', ['filter' => 'permission:leave.settings.manage']);
        $routes->post('leave/settings', 'LeaveSettingsController::update', ['filter' => 'permission:leave.settings.manage']);

        $routes->get('leave/types', 'LeaveTypesController::index', ['filter' => 'permission:leave.type.manage']);
        $routes->get('leave/types/create', 'LeaveTypesController::create', ['filter' => 'permission:leave.type.manage']);
        $routes->post('leave/types', 'LeaveTypesController::store', ['filter' => 'permission:leave.type.manage']);
        $routes->get('leave/types/(:num)/edit', 'LeaveTypesController::edit/$1', ['filter' => 'permission:leave.type.manage']);
        $routes->post('leave/types/(:num)', 'LeaveTypesController::update/$1', ['filter' => 'permission:leave.type.manage']);
        $routes->post('leave/types/(:num)/delete', 'LeaveTypesController::delete/$1', ['filter' => 'permission:leave.type.manage']);

        $routes->get('leave/policies', 'LeavePoliciesController::index', ['filter' => 'permission:leave.policy.manage']);
        $routes->get('leave/policies/create', 'LeavePoliciesController::create', ['filter' => 'permission:leave.policy.manage']);
        $routes->post('leave/policies', 'LeavePoliciesController::store', ['filter' => 'permission:leave.policy.manage']);
        $routes->get('leave/policies/(:num)/edit', 'LeavePoliciesController::edit/$1', ['filter' => 'permission:leave.policy.manage']);
        $routes->post('leave/policies/(:num)', 'LeavePoliciesController::update/$1', ['filter' => 'permission:leave.policy.manage']);
        $routes->post('leave/policies/(:num)/delete', 'LeavePoliciesController::delete/$1', ['filter' => 'permission:leave.policy.manage']);
        $routes->get('leave/policies/(:num)/rules', 'LeavePoliciesController::rules/$1', ['filter' => 'permission:leave.policy.manage']);
        $routes->post('leave/policies/(:num)/rules', 'LeavePoliciesController::saveRules/$1', ['filter' => 'permission:leave.policy.manage']);

        $routes->get('leave/balances', 'LeaveBalancesController::index', ['filter' => 'permission:leave.balance.view']);
        $routes->get('leave/balances/(:num)/adjust', 'LeaveBalancesController::adjustForm/$1', ['filter' => 'permission:leave.balance.adjust']);
        $routes->post('leave/balances/(:num)/adjust', 'LeaveBalancesController::adjust/$1', ['filter' => 'permission:leave.balance.adjust']);

        $routes->get('leave/carry-forward', 'LeaveCarryForwardController::index', ['filter' => 'permission:leave.balance.adjust']);
        $routes->post('leave/carry-forward/run', 'LeaveCarryForwardController::run', ['filter' => 'permission:leave.balance.adjust']);

        $routes->get('leave/encashments', 'LeaveEncashmentsController::index', ['filter' => 'permission:leave.view']);
        $routes->get('leave/encashments/create', 'LeaveEncashmentsController::create', ['filter' => 'permission:leave.create']);
        $routes->post('leave/encashments', 'LeaveEncashmentsController::store', ['filter' => 'permission:leave.create']);
        $routes->post('leave/encashments/(:num)/approve', 'LeaveEncashmentsController::approve/$1', ['filter' => 'permission:leave.balance.adjust']);
        $routes->post('leave/encashments/(:num)/reject', 'LeaveEncashmentsController::reject/$1', ['filter' => 'permission:leave.balance.adjust']);

        $routes->get('leave/calendar', 'LeaveCalendarController::index', ['filter' => 'permission:leave.view']);

        $routes->get('my-leave', 'MyLeaveController::index', ['filter' => 'permission:leave.view.own']);
        $routes->get('my-leave/apply', 'MyLeaveController::apply', ['filter' => 'permission:leave.create']);
        $routes->post('my-leave/apply', 'MyLeaveController::store', ['filter' => 'permission:leave.create']);
        $routes->get('my-leave/applications', 'MyLeaveController::myApplications', ['filter' => 'permission:leave.view.own']);
        $routes->get('my-leave/balance', 'MyLeaveController::balance', ['filter' => 'permission:leave.balance.view.own']);
        $routes->get('my-leave/calendar', 'MyLeaveController::calendar', ['filter' => 'permission:leave.view.own']);
        $routes->get('my-leave/history', 'MyLeaveController::history', ['filter' => 'permission:leave.view.own']);
        $routes->post('my-leave/(:num)/cancel', 'MyLeaveController::cancel/$1', ['filter' => 'permission:leave.cancel']);

        $routes->get('leave/reports', 'LeaveReportsController::index', ['filter' => 'permission:leave.view']);
        $routes->get('leave/reports/(:segment)', 'LeaveReportsController::view/$1', ['filter' => 'permission:leave.view']);
        $routes->get('leave/reports/(:segment)/export/(:segment)', 'LeaveReportsController::export/$1/$2', ['filter' => 'permission:leave.report.export']);
        $routes->post('leave/bulk-approve', 'LeaveApplicationsController::bulkApprove', ['filter' => 'permission:leave.approve']);
        $routes->post('leave/bulk-reject', 'LeaveApplicationsController::bulkReject', ['filter' => 'permission:leave.reject']);
        $routes->post('leave/bulk-cancel', 'LeaveApplicationsController::bulkCancel', ['filter' => 'permission:leave.cancel.any']);
        $routes->get('leave', 'LeaveApplicationsController::index', ['filter' => 'permission:leave.view']);
        $routes->get('leave/(:num)', 'LeaveApplicationsController::view/$1', ['filter' => 'permission:leave.view']);
        $routes->post('leave/(:num)/approve', 'LeaveApplicationsController::approve/$1', ['filter' => 'permission:leave.approve']);
        $routes->post('leave/(:num)/reject', 'LeaveApplicationsController::reject/$1', ['filter' => 'permission:leave.reject']);
        $routes->post('leave/(:num)/cancel', 'LeaveApplicationsController::cancel/$1', ['filter' => 'permission:leave.cancel.any']);
        $routes->post('leave/(:num)/override-approve', 'LeaveApplicationsController::overrideApprove/$1', ['filter' => 'permission:leave.approve']);
        $routes->post('leave/(:num)/override-reject', 'LeaveApplicationsController::overrideReject/$1', ['filter' => 'permission:leave.reject']);

        // Payroll — Salary Components
        $routes->get('payroll/components', 'SalaryComponentsController::index', ['filter' => 'permission:salarycomponent.manage']);
        $routes->get('payroll/components/create', 'SalaryComponentsController::create', ['filter' => 'permission:salarycomponent.manage']);
        $routes->post('payroll/components', 'SalaryComponentsController::store', ['filter' => 'permission:salarycomponent.manage']);
        $routes->get('payroll/components/(:num)/edit', 'SalaryComponentsController::edit/$1', ['filter' => 'permission:salarycomponent.manage']);
        $routes->post('payroll/components/(:num)', 'SalaryComponentsController::update/$1', ['filter' => 'permission:salarycomponent.manage']);
        $routes->post('payroll/components/(:num)/delete', 'SalaryComponentsController::delete/$1', ['filter' => 'permission:salarycomponent.manage']);

        // Payroll — Salary Structures
        $routes->get('payroll/structures', 'SalaryStructuresController::index', ['filter' => 'permission:salarystructure.manage']);
        $routes->get('payroll/structures/create', 'SalaryStructuresController::create', ['filter' => 'permission:salarystructure.manage']);
        $routes->post('payroll/structures', 'SalaryStructuresController::store', ['filter' => 'permission:salarystructure.manage']);
        $routes->get('payroll/structures/(:num)/edit', 'SalaryStructuresController::edit/$1', ['filter' => 'permission:salarystructure.manage']);
        $routes->post('payroll/structures/(:num)', 'SalaryStructuresController::update/$1', ['filter' => 'permission:salarystructure.manage']);
        $routes->post('payroll/structures/(:num)/delete', 'SalaryStructuresController::delete/$1', ['filter' => 'permission:salarystructure.manage']);
        $routes->get('payroll/structures/(:num)/builder', 'SalaryStructuresController::builder/$1', ['filter' => 'permission:salarystructure.manage']);
        $routes->post('payroll/structures/(:num)/save', 'SalaryStructuresController::saveBuilder/$1', ['filter' => 'permission:salarystructure.manage']);
        $routes->post('payroll/structures/(:num)/preview', 'SalaryStructuresController::preview/$1', ['filter' => 'permission:salarystructure.manage']);

        // Payroll — Employee Salary
        $routes->get('payroll/employee-salary', 'PayrollEmployeeSalaryController::index', ['filter' => 'permission:salarystructure.manage']);
        $routes->get('payroll/employee-salary/assign', 'PayrollEmployeeSalaryController::assignForm', ['filter' => 'permission:salarystructure.manage']);
        $routes->get('payroll/employee-salary/assign/(:num)', 'PayrollEmployeeSalaryController::assignForm/$1', ['filter' => 'permission:salarystructure.manage']);
        $routes->post('payroll/employee-salary/assign', 'PayrollEmployeeSalaryController::assign', ['filter' => 'permission:salarystructure.manage']);
        $routes->get('payroll/employee-salary/(:num)/history', 'PayrollEmployeeSalaryController::history/$1', ['filter' => 'permission:salarystructure.manage']);

        // Payroll — Loans
        $routes->get('payroll/loans', 'PayrollLoansController::index', ['filter' => 'permission:loan.manage']);
        $routes->get('payroll/loans/create', 'PayrollLoansController::create', ['filter' => 'permission:loan.manage']);
        $routes->post('payroll/loans', 'PayrollLoansController::store', ['filter' => 'permission:loan.manage']);
        $routes->get('payroll/loans/(:num)/installments', 'PayrollLoansController::installments/$1', ['filter' => 'permission:loan.manage']);
        $routes->post('payroll/loans/(:num)/foreclose', 'PayrollLoansController::foreclose/$1', ['filter' => 'permission:loan.manage']);

        // Payroll — Advances
        $routes->get('payroll/advances', 'PayrollAdvancesController::index', ['filter' => 'permission:advance.manage']);
        $routes->get('payroll/advances/create', 'PayrollAdvancesController::create', ['filter' => 'permission:advance.manage']);
        $routes->post('payroll/advances', 'PayrollAdvancesController::store', ['filter' => 'permission:advance.manage']);
        $routes->post('payroll/advances/(:num)/close', 'PayrollAdvancesController::close/$1', ['filter' => 'permission:advance.manage']);

        // Payroll — Bonus
        $routes->get('payroll/bonus', 'PayrollBonusController::index', ['filter' => 'permission:bonus.manage']);
        $routes->get('payroll/bonus/create', 'PayrollBonusController::create', ['filter' => 'permission:bonus.manage']);
        $routes->post('payroll/bonus', 'PayrollBonusController::store', ['filter' => 'permission:bonus.manage']);
        $routes->post('payroll/bonus/(:num)/delete', 'PayrollBonusController::delete/$1', ['filter' => 'permission:bonus.manage']);

        // Payroll — Incentives
        $routes->get('payroll/incentives', 'PayrollIncentivesController::index', ['filter' => 'permission:bonus.manage']);
        $routes->get('payroll/incentives/create', 'PayrollIncentivesController::create', ['filter' => 'permission:bonus.manage']);
        $routes->post('payroll/incentives', 'PayrollIncentivesController::store', ['filter' => 'permission:bonus.manage']);
        $routes->post('payroll/incentives/(:num)/delete', 'PayrollIncentivesController::delete/$1', ['filter' => 'permission:bonus.manage']);

        // Payroll — Reimbursements
        $routes->get('payroll/reimbursements', 'PayrollReimbursementsController::index', ['filter' => 'permission:reimbursement.manage']);
        $routes->get('payroll/reimbursements/create', 'PayrollReimbursementsController::create', ['filter' => 'permission:reimbursement.manage']);
        $routes->post('payroll/reimbursements', 'PayrollReimbursementsController::store', ['filter' => 'permission:reimbursement.manage']);
        $routes->post('payroll/reimbursements/(:num)/approve', 'PayrollReimbursementsController::approve/$1', ['filter' => 'permission:reimbursement.manage']);
        $routes->post('payroll/reimbursements/(:num)/reject', 'PayrollReimbursementsController::reject/$1', ['filter' => 'permission:reimbursement.manage']);
        $routes->get('payroll/reimbursements/(:num)/download', 'PayrollReimbursementsController::download/$1', ['filter' => 'permission:reimbursement.manage']);

        // Payroll — Arrears
        $routes->get('payroll/arrears', 'PayrollArrearsController::index', ['filter' => 'permission:payroll.edit']);
        $routes->get('payroll/arrears/create', 'PayrollArrearsController::create', ['filter' => 'permission:payroll.edit']);
        $routes->post('payroll/arrears', 'PayrollArrearsController::store', ['filter' => 'permission:payroll.edit']);
        $routes->post('payroll/arrears/(:num)/delete', 'PayrollArrearsController::delete/$1', ['filter' => 'permission:payroll.edit']);

        // Payroll — My Payroll (ESS)
        // Gated by payslip.download, not payroll.view — payroll.view is the admin/HR-side
        // "see the company's payroll runs and every employee's pay" permission and must
        // never be handed to a plain employee; payslip.download is the self-service slug.
        $routes->get('my-payroll', 'MyPayrollController::index', ['filter' => 'permission:payslip.download']);
        $routes->get('my-payroll/payslips', 'MyPayrollController::payslips', ['filter' => 'permission:payslip.download']);
        $routes->get('my-payroll/payslips/(:num)/download', 'MyPayrollController::download/$1', ['filter' => 'permission:payslip.download']);
        $routes->get('my-payroll/salary-summary', 'MyPayrollController::salarySummary', ['filter' => 'permission:payslip.download']);
        $routes->get('my-payroll/loans', 'MyPayrollController::loans', ['filter' => 'permission:payslip.download']);
        $routes->get('my-payroll/advances', 'MyPayrollController::advances', ['filter' => 'permission:payslip.download']);
        $routes->get('my-payroll/reimbursements', 'MyPayrollController::reimbursements', ['filter' => 'permission:payslip.download']);
        $routes->get('my-payroll/tax-summary', 'MyPayrollController::taxSummary', ['filter' => 'permission:payslip.download']);

        // Payroll — Runs
        $routes->get('payroll/runs', 'PayrollRunsController::index', ['filter' => 'permission:payroll.view']);
        $routes->get('payroll/runs/generate', 'PayrollRunsController::generateForm', ['filter' => 'permission:payroll.generate']);
        $routes->post('payroll/runs/generate', 'PayrollRunsController::generate', ['filter' => 'permission:payroll.generate']);
        $routes->get('payroll/runs/(:num)', 'PayrollRunsController::show/$1', ['filter' => 'permission:payroll.view']);
        $routes->post('payroll/runs/(:num)/approve', 'PayrollRunsController::approve/$1', ['filter' => 'permission:payroll.approve']);
        $routes->post('payroll/runs/(:num)/lock', 'PayrollRunsController::lock/$1', ['filter' => 'permission:payroll.lock']);
        $routes->post('payroll/runs/(:num)/unlock', 'PayrollRunsController::unlock/$1', ['filter' => 'permission:payroll.lock']);
        $routes->post('payroll/runs/(:num)/pay', 'PayrollRunsController::pay/$1', ['filter' => 'permission:payroll.pay']);
        $routes->post('payroll/runs/(:num)/cancel', 'PayrollRunsController::cancel/$1', ['filter' => 'permission:payroll.generate']);
        $routes->get('payroll/runs/(:num)/bank-transfer/(:segment)', 'PayrollBankTransferController::export/$1/$2', ['filter' => 'permission:payroll.export']);

        // Payroll — Run Items (per-employee line)
        $routes->get('payroll/run-items/(:num)', 'PayrollRunItemsController::show/$1', ['filter' => 'permission:payroll.view']);
        $routes->post('payroll/run-items/(:num)/adjustments', 'PayrollRunItemsController::addAdjustment/$1', ['filter' => 'permission:payroll.edit']);
        $routes->post('payroll/run-items/(:num)/adjustments/(:num)/delete', 'PayrollRunItemsController::deleteAdjustment/$1/$2', ['filter' => 'permission:payroll.edit']);

        // Payroll — Payslips
        $routes->get('payroll/payslips', 'PayrollPayslipsController::index', ['filter' => 'permission:payroll.view']);
        $routes->get('payroll/payslips/(:num)/download', 'PayrollPayslipsController::download/$1', ['filter' => 'permission:payslip.download.all']);
        $routes->get('payroll/payslips/run/(:num)/zip', 'PayrollPayslipsController::bulkZip/$1', ['filter' => 'permission:payslip.download.all']);

        // Payroll — Reports
        $routes->get('payroll/reports', 'PayrollReportsController::index', ['filter' => 'permission:payroll.view']);
        $routes->get('payroll/reports/(:segment)', 'PayrollReportsController::view/$1', ['filter' => 'permission:payroll.view']);
        $routes->get('payroll/reports/(:segment)/export/(:segment)', 'PayrollReportsController::export/$1/$2', ['filter' => 'permission:payroll.export']);

        // Payroll — Dashboard
        $routes->get('payroll/dashboard', 'PayrollDashboardController::index', ['filter' => 'permission:payroll.view']);

        // Payroll — Settings
        $routes->get('payroll/settings', 'PayrollSettingsController::index', ['filter' => 'permission:payroll.settings.manage']);
        $routes->post('payroll/settings', 'PayrollSettingsController::update', ['filter' => 'permission:payroll.settings.manage']);
        $routes->post('payroll/settings/pf', 'PayrollSettingsController::updatePf', ['filter' => 'permission:payroll.settings.manage']);
        $routes->post('payroll/settings/esi', 'PayrollSettingsController::updateEsi', ['filter' => 'permission:payroll.settings.manage']);
        $routes->post('payroll/settings/tds', 'PayrollSettingsController::updateTds', ['filter' => 'permission:payroll.settings.manage']);
        $routes->post('payroll/settings/pt', 'PayrollSettingsController::storePtSlab', ['filter' => 'permission:payroll.settings.manage']);
        $routes->post('payroll/settings/pt/(:num)', 'PayrollSettingsController::updatePtSlab/$1', ['filter' => 'permission:payroll.settings.manage']);
        $routes->post('payroll/settings/pt/(:num)/delete', 'PayrollSettingsController::deletePtSlab/$1', ['filter' => 'permission:payroll.settings.manage']);

        // Administration
        $routes->get('users', 'UsersController::index', ['filter' => 'permission:users.view']);
        $routes->get('users/create', 'UsersController::create', ['filter' => 'permission:users.create']);
        $routes->post('users', 'UsersController::store', ['filter' => 'permission:users.create']);
        $routes->get('users/(:num)', 'UsersController::view/$1', ['filter' => 'permission:users.view']);
        $routes->get('users/(:num)/edit', 'UsersController::edit/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)', 'UsersController::update/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/activate', 'UsersController::activate/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/suspend', 'UsersController::suspend/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/reset-password', 'UsersController::resetPassword/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/unlock', 'UsersController::unlock/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/lock', 'UsersController::lock/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/set-password', 'UsersController::setPassword/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/expire-password', 'UsersController::expirePassword/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/send-reset-link', 'UsersController::sendResetLink/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/resend-verification', 'UsersController::resendVerification/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/revoke-sessions', 'UsersController::revokeSessions/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/revoke-sessions/(:num)', 'UsersController::revokeSession/$1/$2', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/toggles', 'UsersController::toggles/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/change-role', 'UsersController::changeRole/$1', ['filter' => 'permission:users.edit']);
        $routes->post('users/(:num)/delete', 'UsersController::delete/$1', ['filter' => 'permission:users.delete']);

        $routes->get('roles', 'RolesController::index', ['filter' => 'permission:roles.view']);
        $routes->get('roles/create', 'RolesController::create', ['filter' => 'permission:roles.create']);
        $routes->post('roles', 'RolesController::store', ['filter' => 'permission:roles.create']);
        $routes->get('roles/(:num)/edit', 'RolesController::edit/$1', ['filter' => 'permission:roles.edit']);
        $routes->post('roles/(:num)', 'RolesController::update/$1', ['filter' => 'permission:roles.edit']);
        $routes->post('roles/(:num)/duplicate', 'RolesController::duplicate/$1', ['filter' => 'permission:roles.create']);
        $routes->post('roles/(:num)/archive', 'RolesController::archive/$1', ['filter' => 'permission:roles.delete']);
        $routes->post('roles/(:num)/restore', 'RolesController::restore/$1', ['filter' => 'permission:roles.delete']);
        $routes->get('roles/(:num)/permissions', 'RolesController::permissions/$1', ['filter' => 'permission:roles.edit']);
        $routes->post('roles/(:num)/permissions', 'RolesController::savePermissions/$1', ['filter' => 'permission:roles.edit']);

        $routes->get('permissions', 'PermissionsController::index', ['filter' => 'permission:roles.view']);

        // Settings
        $routes->get('settings', 'SettingsController::index', ['filter' => 'permission:settings.view']);
        $routes->post('settings', 'SettingsController::update', ['filter' => 'permission:settings.manage']);
        $routes->post('branding/(:segment)/upload', 'CompanyBrandingController::upload/$1', ['filter' => 'permission:settings.manage']);
        $routes->get('settings/smtp', 'SmtpSettingsController::index', ['filter' => 'permission:settings.manage']);
        $routes->post('settings/smtp/test', 'SmtpSettingsController::sendTest', ['filter' => 'permission:settings.manage']);
        $routes->get('audit-logs', 'AuditLogsController::index', ['filter' => 'permission:audit.view']);

        // AJAX data sources for searchable selects
        $routes->get('api/users/search', 'Api\UsersController::search', ['filter' => 'permission:users.view']);
        // Shared employee-picker lookup — reused by several modules' forms/filters
        // (Employees, Attendance, Users, Leave policies, Payroll), each gated by
        // its own permission, plus the global command-palette "search employees"
        // widget in layouts/main.php (gated by employee.directory.search, granted
        // to every role including plain Employee — name/code lookup only); "any
        // of" so a user only needs the permission of the page they're actually
        // on, not employee.view as well.
        $routes->get('api/employees/search', 'Api\EmployeesController::search', ['filter' => 'permission:employee.view,employee.create,employee.edit,employee.directory.search,attendance.create,attendance.shift.manage,users.create,users.edit,leave.policy.manage,salarystructure.manage,loan.manage,advance.manage,bonus.manage,reimbursement.manage,payroll.edit']);
    });
});
